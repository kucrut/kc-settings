(function(a){a.fn.kcsbUnique=function(){return this.each(function(){var c=a(this),b=c.val();c.data("olVal",b).blur(function(){var f=a(this),e=c.data("olVal"),d=f.val();if(d!=e&&a.inArray(d,kcsbIDs[f.data("ids")])>-1){f.val("").focus()}})})};a.fn.kcsbCheck=function(){var b=a(this);if((b.attr("name")==="kcsb[id]"&&b.val()==="id")||b.val()===""){b.val("").focus().css("borderColor","#ff0000");return false}else{b.removeAttr("style")}}})(jQuery);jQuery(document).ready(function(b){var d=b(this);var a={sortable:{axis:"y",start:function(e,f){f.placeholder.height(f.item.outerHeight())},stop:function(e,f){f.item.children().each(function(){b("> details > summary > .actions .count",this).text(b(this).index()+1)})}}};b.kcRowCloner();b.kcRowCloner.addCallback("add",function(e){e.nuItem.find(".kc-rows").each(function(){b(this).children(".row").not(":first").remove()});b("input.kcsb-ids",e.nuItem).removeData("olVal").kcsbUnique();if(e.isLast){b("> details > summary > .actions .count",e.nuItem).text(e.nuItem.index()+1)}else{e.block.children().each(function(){b("> details > summary > .actions .count",this).text(b(this).index()+1)})}b("ul.kc-rows").sortable(a.sortable)});b.kcRowCloner.addCallback("del",function(e){if(e.isLast){return}e.block.children().each(function(){b("> details > summary > .actions .count",this).text(b(this).index()+1)})});var c=b("#kcsb");if(!c.is(".hidden")){c.kcGoto()}b("ul.kc-rows").sortable(a.sortable);b(".hasdep",c).kcFormDep();d.on("blur","input.kcsb-slug",function(){var e=b(this);e.val(kcsbSlug(e.val()))});b("input.kcsb-ids").kcsbUnique();d.on("blur","input.required, input.clone-id",function(){b(this).kcsbCheck()});b("#new-kcsb").on("click",function(f){f.preventDefault();c.kcGoto()});b("a.kcsb-cancel").on("click",function(f){f.preventDefault();b("#kcsb").slideUp("slow")});b("a.clone-open").on("click",function(f){f.preventDefault();b(this).parent().children().hide().filter("div.kcsb-clone").fadeIn(function(){b(this).find("input.clone-id").focus()})});b("a.clone-do").on("click",function(g){var f=b(this),h=b(this).siblings("input");if(h.kcsbCheck()===false){return false}f.attr("href",f.attr("href")+"&new="+h.val())});b("input.clone-id").on("keypress",function(g){var f=g.keyCode||g.which;if(f===13){g.preventDefault();b(this).blur().siblings("a.clone-do").click()}});b(".kcsb-tools a.close").on("click",function(g){g.preventDefault();var f=b(this);f.siblings("input").val("");f.parent().fadeOut(function(){b(this).siblings().show()})});b("form.kcsb",c).submit(function(g){var f=true;b(this).find("input.required").not(":disabled").each(function(){if(b(this).kcsbCheck()===false){f=false;return false}});if(!f){return false}})});