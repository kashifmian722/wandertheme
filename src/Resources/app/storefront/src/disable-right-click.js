// Disable right-click on the entire document
document.addEventListener("contextmenu", function (event) {
  event.preventDefault();
});

// If you want to allow right-click on specific elements, you can add the following code:
// Replace ".allow-right-click" with the CSS class of the elements where you want to allow right-click.
const allowedElements = document.querySelectorAll(".allow-right-click");
allowedElements.forEach(function (element) {
  element.addEventListener("contextmenu", function (event) {
    event.stopPropagation();
  });
});
