"use strict";

var WPStagingPro = function ($) {

    var that = {
        isCancelled: false,
        isFinished: false,
        getLogs: false
    };

    // Cache Elements
    var cache = {elements: []};

    /**
     * Get / Set Cache for Selector
     * @param {String} selector
     * @returns {*}
     */
    cache.get = function (selector)
    {
        // It is already cached!
        if ($.inArray(selector, cache.elements) !== -1)
        {
            //console.log('already cached' + cache.elements[selector]);
            return cache.elements[selector];
        }

        // Create cache and return
        cache.elements[selector] = jQuery(selector);

        //console.log(cache.elements[selector]);
        return cache.elements[selector];
    };

    /**
     * Refreshes given cache
     * @param {String} selector
     */
    cache.refresh = function (selector)
    {
        selector.elements[selector] = jQuery(selector);
    };


    /**
     * Ajax Scanning before starting push process
     */
    var startScanning = function ()
    {
        // Scan db and file system
        console.log('Loading WP Staging Pro Initially');
        var $workFlow = cache.get("#wpstg-workflow");


        $workFlow
                // Load scanning data
                .on("click", ".wpstg-push-changes", function (e) {
                    e.preventDefault();

                    var $this = $(this)

                    // Disable button
                    if ($this.attr("disabled"))
                    {
                        return false;
                    }

                    // Add loading overlay
                    $workFlow.addClass("loading");

                    // Get clone id
                    //var cloneID = $this.data("clone");
                    // Get clone id
                    var cloneID = $(this).data("clone");
                    console.log('Clone ID: ' + cloneID);

                    // Prepare data
                    that.data = {
                        action: 'wpstg_scan',
                        clone: cloneID,
                        nonce: wpstg.nonce
                    };

                    // Send ajax request
                    WPStaging.ajax(
                            that.data,
                            function (response) {

                                if (response.length < 1)
                                {
                                    showError(
                                            "Something went wrong! No response.  Go to WP Staging > Settings and lower 'File Copy Limit' and 'DB Query Limit'. Also set 'CPU Load Priority to low '" +
                                            "and try again. If that does not help, " +
                                            "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                                            );
                                }

                                // Styling of elements
                                $workFlow.removeClass("loading").html(response);

                                cache.get(".wpstg-current-step")
                                        .removeClass("wpstg-current-step")
                                        .next("li")
                                        .addClass("wpstg-current-step");

                                cache.get(".wpstg-loader").hide();
                                //cache.get(".wpstg-loader").hide();

                            },
                            "HTML"
                            );
                })
                // Previous Button
                .on("click", ".wpstg-prev-step-link", function (e) {
                    e.preventDefault();
                    WPStaging.loadOverview();
                })
                .on("click", "#wpstg-use-target-dir", function (e) {
                    e.preventDefault();
                    $('#wpstg_clone_dir').val(this.getAttribute('data-path'));

                })
                .on("click", "#wpstg-use-target-hostname", function (e) {
                    e.preventDefault();
                    $('#wpstg_clone_hostname').val(this.getAttribute('data-uri'));
                })
    };


    // Start the whole pushing process
    var startProcess = function () {
        var $workFlow = cache.get("#wpstg-workflow");

        // Click push changes button
        $workFlow.on("click", "#wpstg-push-changes", function (e) {
            e.preventDefault();

            // Hide db tables and folder selection
            cache.get('#wpstg-scanning-db').hide();
            cache.get('#wpstg-scanning-files').hide();

            // Show confirmation modal
            var cloneID = cache.get('#wpstg-push-changes').data("clone");
            var text = "STOP! This will overwrite the live site data and its plugins, themes and media folder with data from the staging site: \"" + cloneID + "\".  \n\nDatabase data will be overwritten for each not excluded table. Take special care if you use a shop system like WooCommerce and check out our FAQ! \n\nIMPORTANT: Before you proceed make sure that you have a backup which includes all database tables. If the pushing process is not succesfull contact us at support@wp-staging.com and we resolve it for you quickly.";

            if (confirmModal(text)) {
                cache.get('#wpstg-push-changes').attr('disabled', true);
                cache.get('.wpstg-prev-step-link').attr('disabled', true);
                cache.get('#wpstg-scanning-files').hide();
                cache.get(".wpstg-current-step")
                    .removeClass("wpstg-current-step")
                    .next("li")
                    .addClass("wpstg-current-step");
                processing();
            }
        });
    };

    /**
     * Start ajax processing
     * @returns string
     */
    var processing = function () {
        console.log("Start ajax processing");

        // Show loader gif
        cache.get(".wpstg-loader").show();

        // Show logging window
        cache.get('.wpstg-log-details').show();

        // Get clone id
        var cloneID = cache.get('#wpstg-push-changes').data("clone");
        console.log(cloneID);

        WPStaging.ajax(
                {
                    action: "wpstg_push_processing",
                    nonce: wpstg.nonce,
                    clone: cloneID,
                    excludedTables: getExcludedTables(),
                    includedDirectories: getIncludedDirectories(),
                    excludedDirectories: getExcludedDirectories(),
                    extraDirectories: getIncludedExtraDirectories()
                },
        function (response)
        {
            // Undefined Error
            if (false === response)
            {
                showError(
                        "Something went wrong! Error: No response.  Go to WP Staging > Settings and lower 'File Copy Limit' and 'DB Query Limit'. Also set 'CPU Load Priority to low '" +
                        "and try again. If that does not help, " +
                        "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                        );
                cache.get(".wpstg-loader").hide();
                return;
            }

            // Throw Error
            if ("undefined" !== typeof (response.error) && response.error) {
                console.log(response.message);
                WPStaging.showError(
                        "Something went wrong! Error: " + response.message + ".  Go to WP Staging > Settings and lower 'File Copy Limit' and 'DB Query Limit'. Also set 'CPU Load Priority to low '" +
                        "and try again. If that does not help, " +
                        "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                        );

                return;
            }

            // Add Log messages
            if ("undefined" !== typeof (response.last_msg) && response.last_msg)
            {
                WPStaging.getLogs(response.last_msg);
            }

            // Continue processing
            if (false === response.status)
            {
                setTimeout(function () {
                    console.log('continue processing');
                    cache.get(".wpstg-loader").show();
                    processing();
                }, wpstg.delayReq);

            } else if (true === response.status) {
                console.log('Processing....');
                processing();
            } else if ('finished' === response.status || ("undefined" !== typeof (response.job_done) && response.job_done)) {
                isFinished(response);
            }
            ;
        },
                "json",
                false
                );
    };

    /**
     * Test database connection
     * @returns object
     */
    var connectDatabase = function () {
        var $workFlow = cache.get("#wpstg-workflow");
        $workFlow.on("click", "#wpstg-db-connect", function (e) {
            e.preventDefault();
            console.log(this);
            cache.get(".wpstg-loader").show();
            cache.get('#wpstg-db-status').hide();
            WPStaging.ajax(
                    {
                        action: "wpstg_database_connect",
                        nonce: wpstg.nonce,
                        databaseUser: cache.get('#wpstg_db_username').val(),
                        databasePassword: cache.get('#wpstg_db_password').val(),
                        databaseServer: cache.get('#wpstg_db_server').val(),
                        databaseDatabase: cache.get('#wpstg_db_database').val(),
                    },
                    function (response)
                    {
                        // Undefined Error
                        if (false === response)
                        {
                            showError(
                                    "Something went wrong! Error: No response." +
                                    "Please try again. If that does not help, " +
                                    "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                                    );
                            cache.get(".wpstg-loader").hide();
                            cache.get('#wpstg-db-status').hide();
                            cache.get('#wpstg-error-details').hide();
                            cache.get('#wpstg-db-connect').after('<span id="wpstg-db-status" class="wpstg-failed"> Failed</span>');
                            return;
                        }

                        // Throw Error
                        if ("undefined" !== typeof (response.errors) && response.errors) {
                            console.log(response.errors);
                            WPStaging.showError(
                                    "Something went wrong! Error: " + response.errors +
                                    "Please try again. If that does not help, " +
                                    "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                                    );
                            cache.get(".wpstg-loader").hide();
                            cache.get('#wpstg-db-status').hide();
                            cache.get('#wpstg-db-connect').after('<span id="wpstg-db-status" class="wpstg-failed"> Failed</span>');
                            return;
                        }

                        if ("undefined" !== typeof (response.success) && response.success) {
                            cache.get(".wpstg-loader").hide();
                            cache.get('#wpstg-db-status').hide();
                            cache.get('#wpstg-error-details').hide();
                            cache.get('#wpstg-db-connect').after('<span id="wpstg-db-status" class="wpstg-success"> Success</span>');
                        }

                    },
                    "json",
                    false
                    );

        });

        // Make form fields editable
        $workFlow.on("click", "#wpstg-ext-db", function (e) {
            if (this.checked) {
                cache.get("#wpstg_db_server").removeAttr('readonly');
                cache.get("#wpstg_db_username").removeAttr('readonly');
                cache.get("#wpstg_db_password").removeAttr('readonly');
                cache.get("#wpstg_db_database").removeAttr('readonly');
                cache.get("#wpstg_db_prefix").removeAttr('readonly');
            } else {
                cache.get("#wpstg_db_server").attr('readonly', true).val('');
                cache.get("#wpstg_db_username").attr('readonly', true).val('');
                cache.get("#wpstg_db_password").attr('readonly', true).val('');
                cache.get("#wpstg_db_database").attr('readonly', true).val('');
                cache.get("#wpstg_db_prefix").attr('readonly', true).val('');
            }

        });

    };


    /**
     * All jobs are finished
     * @param {object} response
     * @returns object
     */
    var isFinished = function (response) {
        console.log('result: ' + response);
        console.log('Finishing .... result: ' + response);
        cache.get(".wpstg-loader").text('Finished');
        cache.get(".wpstg-loader").addClass('wpstg-finished');
        //cache.get(".wpstg-loader").text('Finished');
        //cache.get(".wpstg-loader").addClass('wpstg-finished');
        cache.get('.wpstg-prev-step-link').attr('disabled', false);
        Swal.fire(
            'Pushing successful!',
            'Go to <a href="options-permalink.php">Permalinks</a> and save them again. <br>Delete site cache if required!',
            'success'
        );
    };

    /**
     * Get Excluded (Unchecked) Database Tables
     * @returns {Array}
     */
    var getExcludedTables = function ()
    {
        var excludedTables = [];

        $(".wpstg-db-table input:not(:checked)").each(function () {
            excludedTables.push(this.name);
        });

        return excludedTables;
    };



    /**
     * A confirmation modal
     *
     * @param string
     * @returns {Boolean}
     */
    var confirmModal = function (string) {
        var check = confirm(string);

        if (check === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Included Directories
     * @returns {Array}
     */
    var getIncludedDirectories = function ()
    {
        var includedDirectories = [];

        $(".wpstg-dir input:checked").each(function () {
            var $this = $(this);
            if (!$this.parent(".wpstg-dir").parents(".wpstg-dir").children(".wpstg-expand-dirs").hasClass("disabled"))
            {
                includedDirectories.push($this.val());
            }
        });

        return includedDirectories;
    };

    /**
     * Get Excluded Directories
     * @returns {Array}
     */
    var getExcludedDirectories = function ()
    {
        var excludedDirectories = [];

        $(".wpstg-dir input:not(:checked)").each(function () {
            var $this = $(this);
            //if (!$this.parent(".wpstg-dir").parents(".wpstg-dir").children(".wpstg-expand-dirs").hasClass("disabled"))
            //{
            excludedDirectories.push($this.val());
            //}
        });

        return excludedDirectories;
    };

    /**
     * Get Included Extra Directories
     * @returns {Array}
     */
    var getIncludedExtraDirectories = function ()
    {
        var extraDirectories = [];

        if (!$("#wpstg_extraDirectories").val()) {
            return extraDirectories;
        }

        var extraDirectories = $("#wpstg_extraDirectories").val().split(/\r?\n/);
        //console.log(extraDirectories);

        //excludedDirectories.push($this.val());

        return extraDirectories;
    };

    that.init = function () {
        startProcess();
        startScanning();
        connectDatabase();
    }

    return that;

}(jQuery);


console.log(WPStaging);


jQuery(document).ready(function ($) {
    WPStagingPro.init();
});
