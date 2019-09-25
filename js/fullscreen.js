(function(Drupal) {
  var initialized = false;
  Drupal.behaviors.idix_components_library_fullscreen = {
    attach: function() {
      if (!initialized) {
        initialized = true;
        document
          .querySelectorAll(".icl_component-sample_view")
          .forEach(function(element) {
            new FullscreenView(element);
          });
      }
    }
  };

  function FullscreenView(element) {
    this.element = element;
    this.init();
  }

  FullscreenView.prototype.init = function() {
    this.button = document.createElement("button");
    this.button.innerHTML = "Plein écran";
    this.button.classList.add("icl_fullscreen-button");
    this.button.addEventListener(
      "click",
      this.handleButtonClick.bind(this),
      false
    );
    this.element.appendChild(this.button);

    document.addEventListener(
      "fullscreenchange",
      this.toggleFullscreen.bind(this),
      false
    );
  };

  FullscreenView.prototype.toggleFullscreen = function(event) {
    if (document.fullscreenElement) {
      this.button.innerHTML = "Réduire";
    } else {
      this.button.innerHTML = "Plein écran";
    }
  };

  FullscreenView.prototype.handleButtonClick = function(event) {
    if (!document.fullscreenElement) {
      this.element.requestFullscreen();
    } else if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  };
})(Drupal);
