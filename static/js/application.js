var loader_dw;

var get;
var ladda;
var element_ladda;
var padres_hijos = [];

$(document).ready(() => {
  get = get_vars();
  element_ladda = document.querySelector("button[type=submit]") || document.querySelector("button[type=button]");
});

function get_vars () {
  let url = location.search.replace("?", "");
  let arrUrl = url.split("&");
  let urlObj = {};
  for (let i = 0; i < arrUrl.length; i ++) {
    let x = arrUrl[i].split("=");
    urlObj[x[0]] = x[1];
  }
  return urlObj;
}

function formToJSON (selector) {
  let form = {};
  $(selector).find(":input[name]:checked").each(function () {
    let self = $(this);
    if (self.attr("id") != undefined) {
      let name = self.attr("id");
      if (form[name]) {
        form[name] = form[name] + "," + self.val();
      } else {
        form[name] = self.val();
      }
    }
  });
  let TXTinputs = $(selector).find("input[type=text],input[type=email],input[type=number],input[type=date],input[type=password],input[type=radio],input[type=hidden],select,textarea").filter(function () {
    return this.value != "-1";
  });
  TXTinputs.each(function () {
    let self = $(this);
    if (self.attr("id") != undefined) {
      let name = self.attr("id");
      if (form[name]) {
        form[name] = form[name] + "," + self.val();
      } else {
        form[name] = self.val();
      }
    }
  });
  return form;
}

function urlExists (url) {
  let http = jQuery.ajax({ type: "HEAD", url: url, async: !1 });
  return http.status;
}

function Error_Sistema (texto) {
  new PNotify({
    title: "Error de Sistema",
    text: texto,
    type: "error",
    addclass: "stack-bar-top",
    width: "50%",
    mouse_reset: !1
  });
}

function Notificacion (texto, tipo) {
  PNotify.removeAll();
  new PNotify({ title: "Mensaje del Sistema", text: texto, type: tipo, width: "50%", mouse_reset: !1 });
}

var loader;
var eso;
var mensaje;

$(document).ajaxSend(function (event, request, settings) {

});

$(document).ajaxComplete(function (event, request, settings) {

});
$(document).ajaxError(function (event, xhr, ajaxOptions, thrownError) {
  try {
    ladda.stop();
  } catch (error) {}

});