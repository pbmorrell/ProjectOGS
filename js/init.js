!function(a){skel.init({reset:"full",breakpoints:{global:{range:"*",href:"css/style.css"},desktop:{range:"737-",href:"css/style-desktop.css",containers:1200,grid:{gutters:25}},"1000px":{range:"737-1200",href:"css/style-1000px.css",containers:1e3,grid:{gutters:20},viewport:{width:1080}},mobile:{range:"-736",href:"css/style-mobile.css",containers:"100%",grid:{collapse:!0,gutters:10},viewport:{scalable:!1}}},plugins:{layers:{navPanel:{hidden:!0,breakpoints:"mobile",position:"top-left",side:"left",animation:"pushX",width:"80%",height:"100%",clickToHide:!0,html:'<div data-action="navList" data-args="nav"></div>',orientation:"vertical"},titleBar:{breakpoints:"mobile",position:"top-left",side:"top",height:44,width:"100%",html:'<span class="toggle" data-action="toggleLayer" data-args="navPanel"></span><span class="title" data-action="copyHTML" data-args="logo"></span>'}}}}),a(function(){var c=(a(window),a("form"));c.length>0&&(c.find(".form-button-submit").on("click",function(){return a(this).parents("form").submit(),!1}),skel.vars.IEVersion<10&&(a.fn.n33_formerize=function(){var b=new Array,c=a(this);return c.find("input[type=text],textarea").each(function(){var b=a(this);(""==b.val()||b.val()==b.attr("placeholder"))&&(b.addClass("formerize-placeholder"),b.val(b.attr("placeholder")))}).blur(function(){var b=a(this);b.attr("name").match(/_fakeformerizefield$/)||""==b.val()&&(b.addClass("formerize-placeholder"),b.val(b.attr("placeholder")))}).focus(function(){var b=a(this);b.attr("name").match(/_fakeformerizefield$/)||b.val()==b.attr("placeholder")&&(b.removeClass("formerize-placeholder"),b.val(""))}),c.find("input[type=password]").each(function(){var b=a(this),c=a(a("<div>").append(b.clone()).remove().html().replace(/type="password"/i,'type="text"').replace(/type=password/i,"type=text"));""!=b.attr("id")&&c.attr("id",b.attr("id")+"_fakeformerizefield"),""!=b.attr("name")&&c.attr("name",b.attr("name")+"_fakeformerizefield"),c.addClass("formerize-placeholder").val(c.attr("placeholder")).insertAfter(b),""==b.val()?b.hide():c.hide(),b.blur(function(b){b.preventDefault();var c=a(this),d=c.parent().find("input[name="+c.attr("name")+"_fakeformerizefield]");""==c.val()&&(c.hide(),d.show())}),c.focus(function(b){b.preventDefault();var c=a(this),d=c.parent().find("input[name="+c.attr("name").replace("_fakeformerizefield","")+"]");c.hide(),d.show().focus()}),c.keypress(function(a){a.preventDefault(),c.val("")})}),c.submit(function(){a(this).find("input[type=text],input[type=password],textarea").each(function(b){var c=a(this);c.attr("name").match(/_fakeformerizefield$/)&&c.attr("name",""),c.val()==c.attr("placeholder")&&(c.removeClass("formerize-placeholder"),c.val(""))})}).bind("reset",function(c){c.preventDefault(),a(this).find("select").val(a("option:first").val()),a(this).find("input,textarea").each(function(){var c,b=a(this);switch(b.removeClass("formerize-placeholder"),this.type){case"submit":case"reset":break;case"password":b.val(b.attr("defaultValue")),c=b.parent().find("input[name="+b.attr("name")+"_fakeformerizefield]"),""==b.val()?(b.hide(),c.show()):(b.show(),c.hide());break;case"checkbox":case"radio":b.attr("checked",b.attr("defaultValue"));break;case"text":case"textarea":b.val(b.attr("defaultValue")),""==b.val()&&(b.addClass("formerize-placeholder"),b.val(b.attr("placeholder")));break;default:b.val(b.attr("defaultValue"))}}),window.setTimeout(function(){for(x in b)b[x].trigger("formerize_sync")},10)}),c},c.n33_formerize())),a("#nav > ul").dropotron({offsetY:-16,mode:"fade",noOpenerFade:!0,hideDelay:400})})}(jQuery);