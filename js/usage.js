(function (Drupal) {

  var initialized = false;
  var state = {
    show_code: false
  };
  var input;
  var usages;

  function init (context, settings) {
    if (initialized) {
      return;
    }
    initialized = true;

    usages = document.querySelectorAll('.icl_component-sample_usage');
    usages.forEach(function(element){
      element.style.display = 'none';
    });

    input = document.createElement('input');
    input.type = 'checkbox';

    var label = document.createElement('label');
    label.for = input.id = 'show-code';
    label.appendChild(input);

    var labelText = document.createTextNode('Afficher le code');
    label.appendChild(labelText);

    context.querySelector('.icl_header').appendChild(label);

    checkLocalState();

    input.addEventListener('change', function () {
      updateState({ show_code: input.checked });
    });
  }

  function checkLocalState() {
    if (typeof window.localStorage === 'undefined') {
      return;
    }
    var localState = localStorage.getItem('usage_state');
    localState = localState ? JSON.parse(localState) : state;
    updateState(localState);
    input.checked = localState.show_code;
  }

  function storeLocalState (state) {
    if (typeof window.localStorage === 'undefined') {
      return;
    }
    localStorage.setItem('usage_state', JSON.stringify(state));
  }

  function updateState (newState) {
    if (newState && typeof newState.show_code !== 'undefined' && state.show_code != newState.show_code) {
      state.show_code = newState.show_code;
      storeLocalState(state);

      usages.forEach(function(element){
        element.style.display = state.show_code ? '' : 'none';
      });
    }
  }

  Drupal.behaviors.idix_components_library_usage = {
    attach: init
  };

})(Drupal);
