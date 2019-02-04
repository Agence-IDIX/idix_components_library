(function (Drupal) {
  var initialized = false;
  var head = document.head || document.getElementsByTagName('head')[0];
  var style = document.createElement('style');
  var input = document.querySelector('.icl_search');
  var rule_template = '.icl_component-list a:not([data-name*="{{ needle }}"i]) { display: none; }';

  function updateStyles (needle) {
    var rule = needle ? rule_template.replace('{{ needle }}', needle) : '';
    style.innerHTML = rule;
  }

  Drupal.behaviors.idix_components_library_search = {
    attach: function () {
      if (input && !initialized) {
        initialized = true;
        head.appendChild(style);
        input.addEventListener('keyup', function (e) {
          updateStyles(input.value);
        });
      }
    }
  };


})(Drupal);
