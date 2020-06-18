<style>
    <?php global $jckwds; ?>

    <?php // Default Cells ?>
    body table.jckwds-reserve { background: <?php echo $jckwds->settings['reservations_styling_reservebgcol']; ?>; }
    body table.jckwds-reserve td { border-color:  <?php echo $jckwds->settings['reservations_styling_reservebordercol']; ?>; }
    body table.jckwds-reserve tbody td a { color:  <?php echo $jckwds->settings['reservations_styling_reserveiconcol']; ?>; }
    body table.jckwds-reserve tbody td a:hover { color:  <?php echo $jckwds->settings['reservations_styling_reserveiconhovcol']; ?>; }

    <?php // Header Cells ?>
    body table.jckwds-reserve tr th { background: <?php echo $jckwds->settings['reservations_styling_thbgcol']; ?>; border-color: <?php echo $jckwds->settings['reservations_styling_thbordercol']; ?>; color: <?php echo $jckwds->settings['reservations_styling_thfontcol']; ?>; }
    body table.jckwds-reserve tr th { background: <?php echo $jckwds->settings['reservations_styling_thbgcol']; ?>; border-color: <?php echo $jckwds->settings['reservations_styling_thbordercol']; ?>; color: <?php echo $jckwds->settings['reservations_styling_thfontcol']; ?>; }
    body table.jckwds-reserve thead tr th .jckwds-prevday, body .jckwds-reserve thead tr th .jckwds-nextday { color: <?php echo $jckwds->settings['reservations_styling_tharrcol']; ?>; }
    body table.jckwds-reserve thead tr th .jckwds-prevday:hover, body .jckwds-reserve thead tr th .jckwds-nextday:hover { color: <?php echo $jckwds->settings['reservations_styling_tharrhovcol']; ?>; }

    <?php // Unavailable Cells ?>
    body table.jckwds-reserve tbody td.jckwds_full { background: <?php echo $jckwds->settings['reservations_styling_unavailcell']; ?>; }

    <?php // Reserved Cells ?>
    body table.jckwds-reserve tbody td.jckwds-reserved {  background: <?php echo $jckwds->settings['reservations_styling_reservedbgcol']; ?>; color: <?php echo $jckwds->settings['reservations_styling_reservediconcol']; ?>; }
    body table.jckwds-reserve tbody td.jckwds-reserved strong { border-color: <?php echo $jckwds->settings['reservations_styling_reservedbordercol']; ?>; }
    body table.jckwds-reserve tbody td.jckwds-reserved a { color: <?php echo $jckwds->settings['reservations_styling_reservediconcol']; ?>; }

    <?php // Loading Icon ?>
    body table.jckwds-reserve-wrap .jckwds_loading { color: <?php echo $jckwds->settings['reservations_styling_loadingiconcol']; ?>; }

    <?php // Lock Icon ?>
    body table.jckwds-reserve-wrap .jckwds_loading { color: <?php echo $jckwds->settings['reservations_styling_lockiconcol']; ?>; }
</style>