jQuery(document).ready(function(){
  //jQuery(".page-node-type-page .main-container aside").css({'height':(jQuery(".page-node-type-page .main-container section").height()+'px')});

  window.addEventListener('load', function() {
    new FastClick(document.body);
  }, false);

  //add overlay if it does not exist
  if( jQuery('.overlay').length == 0 ){
      jQuery('body').append('<div class="overlay"></div>');
  }

  //add navbar-nav class for styling homepage
  if(jQuery('.menu-card').length) {
    jQuery('.menu-card ul').addClass('navbar-nav');
  }

  jQuery('.sf-sub-indicator').empty().replaceWith(function(){
    return jQuery("<i class='fa fa-angle-right' aria-hidden='true' />").append(jQuery(this).contents());
  });

  //main menu change fontawesome character
  function isiPhone(){
    return (
      (navigator.platform.indexOf("iPhone") != -1) ||
      (navigator.platform.indexOf("iPod") != -1)
    );
  }

  if(isiPhone()){
    jQuery(".sub-menu").click(function(){
      if ( jQuery(this).find("svg").hasClass('fa-angle-right') ) {
        jQuery(this).find("svg").addClass( 'fa-angle-down' );
      } else {
        jQuery(this).find("svg").removeClass( 'fa-angle-down' ).addClass( 'fa-angle-right' );
      }
    });
  } else {
    jQuery(".sub-menu").hover(function(){
        if ( jQuery(this).find("svg").hasClass('fa-angle-right') ) {
          jQuery(this).find("svg").toggleClass('fa-angle-down');
        }
    }, function(){
      if ( jQuery(this).find("svg").hasClass('fa-angle-down') ) {
        jQuery(this).find("svg").toggleClass('fa-angle-right');
      }
    });
  }


  //accordions change fontawesome character
  jQuery(".panel-title").click(function(){
    if ( jQuery(this).find("svg").hasClass('fa-angle-down') ) {
      jQuery(this).find("svg").addClass( 'fa-angle-up' );
    } else {
      jQuery(this).find("svg").removeClass( 'fa-angle-up' ).addClass( 'fa-angle-down' );
    }
  });


  //redirect if mobile for research database main page
  if((window.location.pathname == '/research-database') && (jQuery(window).width() <= 740)) {
      document.location = "research-database-mobile";
  }

  //jQuery("<i class='fas fa-external-link-alt' aria-hidden='true' />").appendTo(".views-field-field-rdb-url a, .field--name-field-rdb-url a");
  //jQuery(".views-field-field-rdb-url a, .field--name-field-rdb-url a").text("<i class='fas fa-external-link-alt' aria-hidden='true' />");
  jQuery(".views-field-field-rdb-url a, .field--name-field-rdb-url a").empty().append("<i class='fas fa-external-link-alt' aria-hidden='true' />");

  jQuery(".open-nav-btn").click(function(){
    jQuery('.overlay').show();
    jQuery(".open-nav-btn").css("cursor", "auto");
    jQuery(".off-canvas-menu-wrap").css("width", "400px");
  });

  jQuery(".close-nav-btn, .overlay").click(function(){
    jQuery(".off-canvas-menu-wrap").css("width", "0");
    jQuery(".open-nav-btn").css("cursor", "pointer");
    jQuery('.overlay').hide();
  });

  //adds class to all iframe embeds
  //jQuery("iframe").addClass("p-vid");

  //modifies search box tooltip text
  jQuery('.form-search').tooltip('hide')
      .attr('data-original-title', '');

  //add arrow to wysiwyg btn
  if (jQuery(".btn-wysiwyg").length) {
    jQuery(".btn-wysiwyg").append("<i class='fa fa-angle-right' aria-hidden='true' />");
  }

  //jQuery('.sf-accordion-button').prepend('<i class="fa fa-angle-right" aria-hidden="true"></i>');


  /*(function() {
    var $offCanvas = jQuery('#offcanvas'),
        $dropdown  = $offCanvas.find('.dropdown');
    $dropdown.on('show.bs.dropdown', function() {
        jQuery(this).find('.dropdown-menu').slideDown(350);
    }).on('hide.bs.dropdown', function(){
        jQuery(this).find('.dropdown-menu').slideUp(350);
    });
  })();*/


  if((jQuery('.view-research-database')[0])||(jQuery('.view-research-database-mobile')[0])) {
    jQuery('.form-type-textfield').before('<h3 class="rdb-filter-heading rdb-search-heading">Search</h3>');
    jQuery(".form-type-textfield .form-control").attr("placeholder", "Search Keywords");
    jQuery('#views-exposed-form-research-database-page-1').before('<h3 class="rdb-filter-heading">Filter</h3>');
    jQuery('#views-exposed-form-research-database-mobile-page-1').before('<h3 class="rdb-filter-heading">Filter</h3>');
  }

});
