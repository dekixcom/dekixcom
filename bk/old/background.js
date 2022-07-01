
      function getStylesheet() {
      var currentTime = new Date().getHours();
      if (0 <= currentTime&&currentTime < 7) {
        document.write("<link rel='stylesheet' href='night.css' type='text/css'>");
      }
      if (7 <= currentTime&&currentTime < 19) {
        document.write("<link rel='stylesheet' href='day.css' type='text/css'>");
      }
      if (19 <= currentTime&&currentTime < 24) {
        document.write("<link rel='stylesheet' href='night.css' type='text/css'>");
      }
    }

    getStylesheet();
