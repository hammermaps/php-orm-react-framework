/* global coreui */

/**
 * --------------------------------------------------------------------------
 * CoreUI Free Boostrap Admin Template (v3.0.0-beta.1): popovers.js
 * Licensed under MIT (https://coreui.io/license)
 * --------------------------------------------------------------------------
 */
document.querySelectorAll('[data-toggle="popover"]').forEach(function (element) {
  // eslint-disable-next-line no-new
  new coreui.Popover(element);
});