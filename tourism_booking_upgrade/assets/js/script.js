
(function($){
  const cursor = document.querySelector('.cursor');
  const cursorState = {x: window.innerWidth / 2, y: window.innerHeight / 2, tx: window.innerWidth / 2, ty: window.innerHeight / 2};
  let cursorVisible = false;

  function tickCursor(){
    cursorState.x += (cursorState.tx - cursorState.x) * 0.16;
    cursorState.y += (cursorState.ty - cursorState.y) * 0.16;
    if (cursor) cursor.style.transform = `translate(${cursorState.x}px, ${cursorState.y}px) translate(-50%, -50%)`;
    requestAnimationFrame(tickCursor);
  }

  $(document).on('mousemove', function(e){
    cursorState.tx = e.clientX;
    cursorState.ty = e.clientY;
    cursorVisible = true;
  });

  $('a, button, .btn, .package-card, .destination-card, .country-card, .full-hero-card').on('mouseenter', function(){
    $('body').addClass('cursor-hover');
  }).on('mouseleave', function(){
    $('body').removeClass('cursor-hover');
  });

  requestAnimationFrame(tickCursor);

  // Smooth scroll
  $(document).on('click', 'a[href^="#"]', function(e){
    const target = this.getAttribute('href');
    if (target.length > 1 && $(target).length) {
      e.preventDefault();
      $('html, body').animate({scrollTop: $(target).offset().top - 84}, 700);
      $('.navbar-collapse').collapse('hide');
    }
  });

  // Navbar active & shrink
  const nav = $('.luxury-nav');
  function navState(){
    nav.toggleClass('scrolled', window.scrollY > 8);
  }
  navState();
  $(window).on('scroll', navState);

  // Reveal on scroll
  const revealObserver = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting) entry.target.classList.add('show');
    });
  }, { threshold: 0.14 });
  $('.reveal').each(function(){ revealObserver.observe(this); });

  // Hero slideshow
  const slides = $('.hero-bg');
  let current = 0;
  function showSlide(idx){
    slides.removeClass('active').css('transform','scale(1.08)');
    const slide = slides.eq(idx);
    slide.addClass('active');
    current = idx;
  }
  if (slides.length){
    showSlide(0);
    setInterval(function(){
      const next = (current + 1) % slides.length;
      showSlide(next);
    }, 6500);
  }

  // Parallax tilt for selected hero areas
  $('.parallax-layer').each(function(){
    const el = this;
    $(el).closest('.hero-shell, .full-hero-card, .login-visual').on('mousemove', function(e){
      const rect = this.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      el.style.transform = `translate3d(${x * 16}px, ${y * 16}px, 0)`;
    }).on('mouseleave', function(){
      el.style.transform = 'translate3d(0,0,0)';
    });
  });

  // Package filter
  $('[data-filter]').on('click', function(){
    const filter = ($(this).data('filter') || '').toString();
    $('[data-filter]').removeClass('active');
    $(this).addClass('active');
    if (filter === 'all') {
      $('[data-item]').fadeIn(180);
    } else {
      $('[data-item]').each(function(){
        const item = ($(this).data('item') || '').toString();
        const matches = item === filter || item.includes(filter) || (filter === 'Americas' && item.includes('America'));
        $(this).toggle(matches);
      });
    }
  });

  // Search / jump
  $('#heroSearch').on('submit', function(e){
    e.preventDefault();
    const val = ($('#searchInput').val() || '').toLowerCase().trim();
    if (!val) return;
    const match = $('[data-search]').filter(function(){ return $(this).data('search').toLowerCase().includes(val); }).first();
    if (match.length) {
      $('html, body').animate({scrollTop: match.offset().top - 92}, 700);
      match.addClass('shadow-lg');
      setTimeout(()=>match.removeClass('shadow-lg'), 1200);
    }
  });

  // Login/register toggle
  $('[data-auth-tab]').on('click', function(){
    const tab = $(this).data('auth-tab');
    $('[data-auth-tab]').removeClass('active');
    $(this).addClass('active');
    $('.auth-panel').hide();
    $(`.auth-panel[data-panel="${tab}"]`).fadeIn(150);
  });

  // Booking AJAX
  $('#bookingForm').on('submit', function(e){
    e.preventDefault();
    const btn = $(this).find('button[type="submit"]');
    const original = btn.html();
    btn.prop('disabled', true).html('Sending...');
    $.ajax({
      url: this.action,
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json'
    }).done(function(res){
      $('#bookingSuccess, #bookingError').addClass('d-none').text('');
      if (res.success) {
        $('#bookingSuccess').removeClass('d-none').text(res.message);
        $('#bookingForm')[0].reset();
      } else {
        $('#bookingError').removeClass('d-none').text(res.message || 'Something went wrong');
      }
    }).fail(function(){
      $('#bookingError').removeClass('d-none').text('Request failed. Check your local server.');
    }).always(function(){
      btn.prop('disabled', false).html(original);
    });
  });

  // Hide alerts after a while
  setTimeout(function(){ $('.alert').not('#bookingSuccess').fadeOut(400); }, 6500);
})(jQuery);
