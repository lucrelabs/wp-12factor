/*                    ,-,-
                     / / |
   ,-'             _/ / /
  (-_          _,-' `Z_/
   "#:      ,-'_,-.    \  _
    #'    _(_-'_()\     \" |
  ,--_,--'                 |
 / ""                      L-'\
 \,--^---v--v-._        /   \ |
   \_________________,-'      |
                    \
                     \
                      \
 NOTE: The code in this file will be executed multiple times! */

let top_window = window;
let is_iframe  = false;

if (window.top && window.top.__Cypress__) {
  if (window.parent === window.top) {
    top_window = window;
    is_iframe  = false;

  } else {
    top_window = window.parent;
    is_iframe  = true;
  }

} else if (window.top) {
  top_window = window.top;
  is_iframe  = window.top !== window.self;
}


export {
  top_window,
  is_iframe,
};
