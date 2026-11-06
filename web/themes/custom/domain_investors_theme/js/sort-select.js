/**
 * @file
 * Follows the link a sort select's chosen option names.
 *
 * Without JavaScript the same choices are links, in a <noscript> beside it.
 */
((Drupal, once) => {
  Drupal.behaviors.portfolioSortSelect = {
    attach(context) {
      once('portfolio-sort', 'select[data-follow]', context).forEach((select) => {
        select.addEventListener('change', () => {
          window.location.assign(select.value);
        });
      });
    },
  };
})(Drupal, once);
