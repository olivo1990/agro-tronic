function get_vars(){
    var url= location.search.replace("?", "");
    var arrUrl = url.split("&");
    var urlObj={};   
    for(var i=0; i<arrUrl.length; i++){
        var x= arrUrl[i].split("=");
        urlObj[x[0]]=x[1]
    }
    return urlObj;
}

function CargarMenu()
{
    var Datos_Menu = JSON.parse(localStorage.getItem('gevents_men'));
    var MENU = Datos_Menu['MENU'];
    var SUBMENU = Datos_Menu['SUBMENU'];
    var html_menu = '<li class="nav-parent">';
    $.each(MENU,function(men_indice, men_item){
        var id_men  = men_item.men_id;
        html_menu += '<a href="'+men_item.men_url+'"><i class="'+men_item.men_icon+'"></i><span data-translate="builder">'+men_item.men_nombre+'</span> <span class="fa arrow"></span></a>';
        html_menu += '<ul class="children collapse">';
        $.each(SUBMENU,function(sub_indice,sub_item){
            var id_men_submen = sub_item.subm_men_id;
            if(id_men_submen == id_men)
            {
               html_menu += '<li><a href="'+sub_item.subm_url+'">'+sub_item.subm_nombre+'</a></li>'; 
            }
        });
        html_menu += '</ul>';
        html_menu += '</li>';
    });

    $("#menu_app").html(html_menu);
}
//Form to Json
function formToJSON( selector ){
         var form = {};
         $(selector).find(':input[name]:checked').each( function() {
           var self = $(this);
           if(self.attr('name') != undefined)
           {
             var name = self.attr('name');
              if (form[name]) {
                form[name] = form[name] + ',' + self.val();
              }
              else {
                form[name] = self.val();
              }
           }
           
        });

         var TXTinputs =$(selector).find('input[type=text],input[type=email],input[type=password],input[type=radio],select,textarea').filter(function() {
           return this.value!='-1';
         });

         TXTinputs.each( function() {
           var self = $(this);
           if(self.attr('name') != undefined)
           {
               var name = self.attr('name');
                if (form[name]) {
                  form[name] = form[name] + ',' + self.val();
                }
                else {
                   form[name] = self.val();
                }
          }
        });

         return form;
     }
//Fin Form to Json