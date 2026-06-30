/* Linden Court — header scroll state + fullscreen mobile menu. */
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var header = document.querySelector('.lc-header');
    var burger = document.querySelector('.lc-burger');
    var menu = document.getElementById('lc-mobile-menu');

    function onScroll() {
      if (!header) return;
      if (window.scrollY > 24) header.classList.add('is-scrolled');
      else header.classList.remove('is-scrolled');
    }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    if (burger && menu) {
      burger.addEventListener('click', function () {
        var open = menu.classList.toggle('is-open');
        burger.classList.toggle('is-active', open);
        document.body.style.overflow = open ? 'hidden' : '';
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      menu.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
          menu.classList.remove('is-open');
          burger.classList.remove('is-active');
          document.body.style.overflow = '';
          burger.setAttribute('aria-expanded', 'false');
        });
      });
    }
  });
})();
