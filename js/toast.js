// Global toast handler for PT Samson Sure auth pages
// Requires jQuery and Toastr to be loaded before this script.

(function () {
    if (typeof toastr === "undefined") {
      console.warn("Toastr is not loaded. toast.js will not run.");
      return;
    }
  
    toastr.options = {
      closeButton: true,
      progressBar: true,
      positionClass: "toast-top-right",
      timeOut: "4000",
    };
  
    var messages = window.APP_MESSAGES || {};
  
    if (messages.success) {
      toastr.success(messages.success);
    }
  
    if (messages.error) {
      toastr.error(messages.error);
    }
  })();