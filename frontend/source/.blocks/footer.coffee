$(document)
  .on 'mouseenter', '.page-footer-group', (e) ->
    $('.page-footer-group')
      .not this
      .mod 'inactive', true

  .on 'mouseleave', '.page-footer-group', (e) ->
    $('.page-footer-group').mod 'inactive', false

$('.js-popup').magnificPopup
  type: 'inline'
  showCloseBtn: false


$(document)
  .on 'click', '.js-popup-close', (e) ->
    e.preventDefault()
    $.magnificPopup.close()
