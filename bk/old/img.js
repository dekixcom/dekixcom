/*
This is a JavaScript (JS) file.
JavaScript is the programming language that powers the web.

To use this file, place the following <script> tag just before the closing </body> tag in your HTML file, making sure that the filename after "src" matches the name of your file...

    <script src="script.js"></script>

Learn more about JavaScript at https://developer.mozilla.org/en-US/Learn/JavaScript

When you're done, you can delete all of this grey text, it's just a comment.
*/

function changeImg(){
  var currentTime = new Date().getHours();
  if (0 <= currentTime&&currentTime < 7) {
    document.write("<img src='img/dekix_night.png'>");
  }
  if (7 <= currentTime&&currentTime < 19) {
    document.write("<img src='img/dekix_day.png'>");
  }
  if (19 <= currentTime&&currentTime < 24) {
    document.write("<img src='img/dekix_night.png'>");
  }
}

changeImg();

