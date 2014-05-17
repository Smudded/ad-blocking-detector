/**
 * This file simply has a name that should upset an ad blocker, and inputs a div
 * in the DOM that should really set them off. To see if this
 * file is blocked, it set's a flag The 
 * adblock-detector.js file can then hunt for this flag and element to see
 * if an ad blocker exists.
 */
window.abd_script_load_flag = true;
var abd_script_load_created = document.createElement("div");
abd_script_load_created.setAttribute("id", "abd_script_load_flag");