jQuery(window).bind("pageshow", function(event) {
  if (event.originalEvent.persisted) {
      window.location.reload()
  }
});

jQuery(document).ready(function(){

  //activates lightbox effect on gallery content type
  jQuery(document).on('click', '[data-toggle="lightbox"]', function(event) {
    event.preventDefault();
    jQuery(this).ekkoLightbox();
  });

  //set sidebar height to match height of content on page
  if (jQuery(".page-node-type-page .main-container aside .region-sidebar-menu").css('display') != 'none') {
    if(jQuery(".page-node-type-page .main-container section").height() > jQuery(".page-node-type-page .main-container aside").height()) {
      setTimeout(function(){ jQuery(".page-node-type-page .main-container aside").css({'min-height':(jQuery(".page-node-type-page .main-container section").height()+'px')}); }, 1000);
    }
  }

  if(jQuery(".path-people .main-container section").height() > jQuery(".path-people .main-container aside").height()) {
    jQuery(".path-people .main-container aside").css({'min-height':(jQuery(".path-people .main-container section").height()+'px')});
  }

  if(jQuery(".page-node-type-newsletter-article .main-container section").height() > jQuery(".page-node-type-newsletter-article .main-container aside").height()) {
    jQuery(".page-node-type-newsletter-article .main-container aside").css({'min-height':(jQuery(".page-node-type-newsletter-article .main-container section").height()+'px')});
  }

  if(jQuery(".page-node-type-research .main-container section").height() > jQuery(".page-node-type-research aside").height()) {
    jQuery(".page-node-type-research .main-container aside").css({'min-height':(jQuery(".page-node-type-research .main-container section").height()+'px')});
  }

  if(jQuery(".page-node-type-gallery .main-container section").height() > jQuery(".page-node-type-gallery aside").height()) {
    jQuery(".page-node-type-gallery .main-container aside").css({'min-height':(jQuery(".page-node-type-gallery .main-container section").height()+'px')});
  }

  if(jQuery(".page-node-type-course .main-container section").height() > jQuery(".page-node-type-course aside").height()) {
    jQuery(".page-node-type-course .main-container aside").css({'min-height':(jQuery(".page-node-type-course .main-container section").height()+'px')});
  }


  //show/hide decription for accessibility menu
  jQuery("#superfish-main > li:first-child").focusin(function(){
    jQuery("#main-nav-menubar-instructions").css("display", "block");
  });
  jQuery("#superfish-main > li:first-child").focusout(function(){
    jQuery("#main-nav-menubar-instructions").css("display", "none");
  });

  //adds fontawesome arrow to menu

  //check if is iPhone
  function isiPhone(){
    return (
      (navigator.platform.indexOf("iPhone") != -1) ||
      (navigator.platform.indexOf("iPod") != -1)
    );
  }

  //check if menu in mobile view
  if (jQuery('#superfish-main-accordion')[0]){
    jQuery('.sf-accordion-button .svg-inline--fa').empty().replaceWith(function(){
      return jQuery("<i class='fa fa-angle-down' aria-hidden='true' />").append(jQuery(this).contents());
    });

    //change arrow direction on click
    jQuery('.sf-accordion-button').click(function(){
      if ( jQuery(this).find("svg").hasClass('fa-angle-down') ) {
        jQuery(this).find("svg").addClass( 'fa-angle-right' );
      } else {
        jQuery(this).find("svg").removeClass( 'fa-angle-right' ).addClass( 'fa-angle-down' );
      }
      if ( jQuery(this).find("i").hasClass('fa-angle-down') ) {
        jQuery(this).find("i").removeClass('fa-angle-down').addClass( 'fa-angle-right' );
      } else {
        jQuery(this).find("i").removeClass( 'fa-angle-right' ).addClass( 'fa-angle-down' );
      }
    });


  } else {
    jQuery('.sf-sub-indicator').empty().replaceWith(function(){
      return jQuery("<i class='fa fa-angle-right' aria-hidden='true' />").append(jQuery(this).contents());
    });
  }

  //add arrow to people filter taxonomy
  if(jQuery("#views-exposed-form-people-directory-page-1").length) {
    jQuery(".panel-title").append("<i class='fa fa-angle-down' aria-hidden='true'></i>");
  }

  //accordions change fontawesome character
  jQuery(".panel-title").click(function(){
    //jQuery(this).find("svg").toggleClass('fa-angle-up', addOrRemove);
    if ( jQuery(this).find("svg").hasClass('fa-angle-down') ) {
      jQuery(this).find("svg").addClass( 'fa-angle-up' );
    } else {
      jQuery(this).find("svg").removeClass( 'fa-angle-up' ).addClass( 'fa-angle-down' );
    }
    if ( jQuery(this).find("i").hasClass('fa-angle-down') ) {
      jQuery(this).find("i").removeClass('fa-angle-down').addClass( 'fa-angle-up' );
    } else {
      jQuery(this).find("i").removeClass( 'fa-angle-up' ).addClass( 'fa-angle-down' );
    }
  });

  //add class to last accordion in group
  jQuery(".paragraph--type--accordion .panel:last-child").addClass( 'yell' );

  //open search form in main menu
  jQuery(".search-icon, .search-icon-mobile").click(function() {
    jQuery(".search-block-form").toggle();
    jQuery(".search-block-form input[type='text']").focus();
  }).focus(function() {
    jQuery(".search-block-form").toggle();
    jQuery(".search-block-form input").focus();
  });


//checking if logos exist and setting up header
  jQuery('#dep-logo-img').on('error', function(){
        jQuery('.dep-logo img').replaceWith('<img src="/themes/asc_bootstrap/images/logos/demo-logos/department-logo.svg"/>');
        jQuery('#header-logos-mobile .dep-logo img').replaceWith('<img src="/themes/asc_bootstrap/images/logos/demo-logos/department-logo-mobile.svg"/>');
    });

//clear search box on people directory on user focus
  jQuery('#views-exposed-form-people-directory-page-1 #edit-combine').focus(function(){
    jQuery('#edit-combine').val('');

    jQuery(this).blur(function(){
      jQuery('#views-exposed-form-people-directory-page-1').submit();
    });
  });


  //add class if people directory
  /*if ( jQuery('#block-exposedformpeople-directorypage-1').length ) {
    jQuery('aside.col-sm-3').addClass('col-sm-push-9');
    jQuery('section.col-sm-9').addClass('col-sm-pull-3');
  }*/

  //redirect if mobile for people directory main page
  // if(((window.location.pathname == '/people')||(window.location.pathname == '/directory')) && (jQuery(window).width() <= 740)) {
  //     document.location = "people-mobile";
  // }
  //redirect if mobile for research directory main page
  if((window.location.pathname == '/research-project-directory') && (jQuery(window).width() <= 740)) {
      document.location = "research-project-directory-mobile";
  }
  //redirect if mobile for courses main page
  if((window.location.pathname == '/courses') && (jQuery(window).width() <= 740)) {
      document.location = "courses-mobile";
  }


  //sidebar arrows
  if (jQuery(".sidebar-nav .active-trail").length) {
    jQuery(".sidebar-nav .active-trail").find("i:first").removeClass('fa-angle-right').addClass( 'fa-angle-down' );
  }

  jQuery(".sidebar-nav li").click(function(e){
    e.stopPropagation();
    if ( jQuery(this).find("ul:first").css('display') == 'none' ) {
      jQuery(this).find("i:first").removeClass('fa-angle-right').addClass( 'fa-angle-down' );
    } else {
      jQuery(this).find("i:first").removeClass( 'fa-angle-down' ).addClass( 'fa-angle-right' );
    }
    jQuery(this).find("ul:first").slideToggle();
  });

  if (jQuery(".is-active").length) {
    jQuery( ".is-active" ).parent().addClass( "active-link" );
    jQuery( ".is-active" ).parent().parent().addClass( "active-link-li" );
    jQuery( ".is-active" ).parent().parent().parent().addClass( "active-link-ul" );
    jQuery("ul li ul:has(.active-link-ul)").addClass( "parent-active-link-ul" );
  }


  if (jQuery(".sidebar-nav .active-link-li").length) {
    jQuery(".sidebar-nav .active-link-li").find("i:first").removeClass('fa-angle-right').addClass( 'fa-angle-down' );
  }


  if(jQuery("#views-exposed-form-events-page-1").length) {

    var fullDate = new Date(),
    twoDigitMonth = fullDate.getMonth()+1;

    if(twoDigitMonth <= 9) {
      twoDigitMonth = '0' + twoDigitMonth;
    }

    var currentDate = twoDigitMonth + "/" + fullDate.getDate() + "/" + fullDate.getFullYear();

    if(jQuery("#views-exposed-form-events-page-1 #edit-field-evt-date-range-end-value-1").val() == "now") {
      jQuery("#views-exposed-form-events-page-1 #edit-field-evt-date-range-end-value-1").val(currentDate);
    }
    if(jQuery("#views-exposed-form-events-page-1 #edit-field-evt-date-range-end-value-1").val() == "") {
      jQuery("#views-exposed-form-events-page-1 #edit-field-evt-date-range-end-value-1").val(currentDate);
    }

  }


  //add arrow to wysiwyg btn
  if (jQuery(".btn-wysiwyg").length) {
    jQuery(".btn-wysiwyg").append("<i class='fa fa-angle-right' aria-hidden='true' />");
  }


  //adds new class to images that have captions so that they are sized appropriately
  if (jQuery("figure").length) {
    jQuery("figure").each(function(){
      var quickEditName = jQuery(this).find('img').attr('src');
      var arr= quickEditName.split('/');
      jQuery(this).addClass( "wrapper-" + arr[5] );
    });
  }

  //check for anchor hash in url and if exists open accordion
  var accordionAnchor = window.location.hash;
  var accordAnchorTrimmed = accordionAnchor.substring(1);

  if (accordionAnchor.length) {
    var accordDiv = jQuery('[id="'+accordAnchorTrimmed+'"]').parent().parent().parent().parent().parent().attr('id'),
        pattern = /collapse/,
        exists = pattern.test(accordDiv);
    if(exists) {
      jQuery(window).scrollTop(jQuery('#'+accordDiv).offset().top);
      jQuery('#'+accordDiv).addClass('in');
    }
  }

});

// window.addEventListener("load", function(event) {
//   var options = {
//     'ariaLabel' : 'Main Navigation',
//     'mode' : 'dualAction'
//   }
//   var test = new a11yNavbar('main-nav', options);
// });


// When the user scrolls the page, execute addStickyNav
window.onscroll = function() {addStickyNav()};

// Get the navbar
var navbar = document.getElementById("main-nav");

// Get the offset position of the navbar
var sticky = navbar.offsetTop;

// Add the sticky class to the navbar when you reach its scroll position. Remove "sticky" when you leave the scroll position
function addStickyNav() {
  if (window.pageYOffset >= sticky) {
    navbar.classList.add("sticky")
  } else {
    navbar.classList.remove("sticky");
  }
}
