Drupal.drupalchat = Drupal.drupalchat || {};

function sendMessages() {
	jQuery.post(Drupal.settings.drupalchat.sendUrl, {
   		drupalchat_uid2: drupalchat.send_current_uid2, 
   		drupalchat_message: drupalchat.send_current_message 
	});
}


function checkChatBoxInputKey(event, chatboxtextarea, chatboxtitle) {
        //alert('ici');
        if(event.keyCode == 13 && event.shiftKey == 0)  {

                message = jQuery(chatboxtextarea).val();
                message = message.replace(/^\s+|\s+$/g,"");
                message = message.substr(0,255);
                jQuery(chatboxtextarea).val('');
                jQuery(chatboxtextarea).focus();
                jQuery(chatboxtextarea).css('height','44px');

                var currentTime = new Date();
                var hours = currentTime.getHours();
                var minutes = currentTime.getMinutes();
                if (hours < 10) {
                        hours = "0" + hours;
                }
                if (minutes < 10) {
                        minutes = "0" + minutes;
                }


                if (message != '') {
                        if(Drupal.settings.drupalchat.polling_method == '0') {
                                drupalchat.send_current_uid2 = chatboxtitle;
                                if (drupalchat.attach_messages_in_queue == 0) {
                                        setTimeout(function() {
                                                sendMessages();
                                                drupalchat.attach_messages_in_queue = 0;
                                        }, (Drupal.settings.drupalchat.send_rate) * 1000);

                                        drupalchat.send_current_message = message;
                                        drupalchat.attach_messages_in_queue = 1;
                                }
                                else {
                                        drupalchat.send_current_message += '{{drupalchat_newline}}' + message;
                                }
                        }
                        else {
                                drupalchat.send_current_uid2 = chatboxtitle;
                                drupalchat.send_current_message = message;
                                sendMessages();
                        }
                        message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                        message = emotify(message);
                        if (jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent .chatboxusername a:last").html() == Drupal.settings.drupalchat.username) {
                                jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<p>'+message+'</p>');
                        }
                        else {
                                jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxusername"><span class="chatboxtime">'+hours+':'+minutes+'</span><a href="'+Drupal.settings.basePath+'user/'+Drupal.settings.drupalchat.uid+'">'+Drupal.settings.drupalchat.username+'</a></div><p>'+message+'</p>');
                        }

                        jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop(jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
                }
                return false;
        }

        var adjustedHeight = chatboxtextarea.clientHeight;
        var maxHeight = 94;

        if (maxHeight > adjustedHeight) {
                adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
                if (maxHeight)
                        adjustedHeight = Math.min(maxHeight, adjustedHeight);
                if (adjustedHeight > chatboxtextarea.clientHeight)
                        jQuery(chatboxtextarea).css('height', adjustedHeight+8 + 'px');
        } else {
                jQuery(chatboxtextarea).css('overflow', 'auto');
        }
        return true;
    }


var drupalchat = {
	username: null,
	uid: null,
	send_current_message: null,
	last_timestamp: 0,
	send_current_uid2: 0,
	attach_messages_in_queue: 0,
	running: 0,
	online_users: 0,
	smilies: { /*
    smiley     image_url          title_text              alt_smilies           */
    ":)":    [ "glad.png",        "happy",                ":-)"                 ],
    ":(":    [ "sad.png",         "sad",                  ":-("                 ],
    ";)":    [ "wink.png",        "winking",              ";-)"                 ],
    ":D":    [ "lol.png",         "big grin",             ":-D"                 ],
    "^_^":   [ "muhaha.gif",      "happy eyes"                                  ],
    ">:o":   [ "haha.gif",        "laughing eyes"                               ],
    ":3":    [ "hohoho.png",      "laughing eyes"                               ],
    ">:-(":  [ "waiting.png",     "grumpy",               "X-(","X(","x-(","x(" ],
    ":'(":   [ "crying.png",      "crying"                                      ],
    ":o":    [ "omg.png",         "shocked",              ":-o"                 ],
    "8)":    [ "cool.png",        "glass",                "8-)","B)","B-)"      ],
    "8-|":   [ "ouch.png",        "cool",                 "8-|"                 ],
    ":p":    [ "tongue.png",      "tongue",               ":-p",":P",":-P"      ],
    "O.o":   [ "stupid-idea.png", "woot?!"                                      ],
    "-_-":   [ "huh-where.png",   "dark emote"                                  ],
    ":/":    [ "oopsy.png",       "duhhh",                ":-/",":\\",":-\\"    ],
    ":*":    [ "kiss.png",        "kiss",                 ":-*"                 ],
    "<3":    [ "love.png",        "heart",                                      ],
    "3:)":   [ "very-devil-plan.png", "devil smile"                                 ],
    "O:)":   [ "idiotic-smile.png",   "angel"                                       ]
  }
};
//(function ($) {

    jQuery(document).ready(function(){
	YUI().use('gallery-storage-lite', function (Y) {
	    Y.StorageLite.on('storage-lite:ready', function () {
	        if (drupalchat_getCookie('DRUPALCHAT_NEWLOGIN') != 1) {
	          if(Y.StorageLite.getItem('username')!=null) {
	        	drupalchat.username = Y.StorageLite.getItem('username');
	          }
	          if(Y.StorageLite.getItem('uid')!=null) {
	        	drupalchat.uid = Y.StorageLite.getItem('uid');
	          }
	          if(Y.StorageLite.getItem('send_current_message')!=null) {
	        	drupalchat.send_current_message = Y.StorageLite.getItem('send_current_message');
	          }	
	          if(Y.StorageLite.getItem('last_timestamp')!=null) {
	        	drupalchat.last_timestamp = Y.StorageLite.getItem('last_timestamp');
	          }
	          if(Y.StorageLite.getItem('send_current_uid2')!=null) {
	        	drupalchat.send_current_uid2 = Y.StorageLite.getItem('send_current_uid2');
	          }
	          if(Y.StorageLite.getItem('attach_messages_in_queue')!=null) {
	        	drupalchat.attach_messages_in_queue = Y.StorageLite.getItem('attach_messages_in_queue');
	          }
	          if(Y.StorageLite.getItem('running')!=null) {
	        	drupalchat.running = Y.StorageLite.getItem('running');
	          }
	          if(Y.StorageLite.getItem('drupalchat')!=null) {
	        	if(Y.StorageLite.getItem('drupalchat').length > 4) {
	        		jQuery('#drupalchat').html(Y.StorageLite.getItem('drupalchat'));
					if(Drupal.settings.drupalchat.polling_method == '2') {
					  jQuery('#chatpanel .subpanel ul').empty();
					  jQuery('#chatpanel .online-count').html(jQuery('#chatpanel .subpanel ul > li').size());
					}
	        	}
	          }
	        }
	        else {
	          drupalchat_setCookie('DRUPALCHAT_NEWLOGIN', 2, 0);
	        }

	    });

	});
	//Load smileys.
	emotify.emoticons( Drupal.settings.drupalchat.smileyURL, drupalchat.smilies );
	//Adjust panel height
	jQuery.fn.adjustPanel = function(){
	    jQuery(this).find("ul, .subpanel").css({ 'height' : 'auto'}); //Reset sub-panel and ul height
	
	    var windowHeight = jQuery(window).height(); //Get the height of the browser viewport
	    var panelsub = jQuery(this).find(".subpanel").height(); //Get the height of sub-panel
	    var panelAdjust = windowHeight - 100; //Viewport height - 100px (Sets max height of sub-panel)
	    var ulAdjust =  panelAdjust - 25; //Calculate ul size after adjusting sub-panel
	
	    if (panelsub > panelAdjust) {	 //If sub-panel is taller than max height...
	        jQuery(this).find(".subpanel").css({ 'height' : panelAdjust}); //Adjust sub-panel to max height
	        jQuery(this).find("ul").css({ 'height' : panelAdjust}); ////Adjust subpanel ul to new size
	    }
	    else { //If sub-panel is smaller than max height...
	    	jQuery(this).find("ul").css({ 'height' : 'auto'}); //Set sub-panel ul to auto (default size)
	    }
	};
	
	//Execute function on load
	jQuery("#chatpanel").adjustPanel(); //Run the adjustPanel function on #chatpanel
	
	//Each time the viewport is adjusted/resized, execute the function
	jQuery(window).resize(function () {
	    jQuery("#chatpanel").adjustPanel();
	});
	
	//Add sound effect SWF file to document
	jQuery("<div><object id=\"drupalchatbeep\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"1\" height=\"1\"><param name=\"movie\" value=\"" + Drupal.settings.drupalchat.sound + "\"/><!--[if !IE]>--><object type=\"application/x-shockwave-flash\" data=\"" + Drupal.settings.drupalchat.sound + "\" width=\"1\" height=\"1\"></object><!--<![endif]--></object></div>").appendTo(jQuery("body"));
	swfobject.registerObject("drupalchatbeep", "9");
	
	//Click event on subpanels	
	jQuery("#mainpanel li a.subpanel_toggle, .chatbox a.chatboxhead").live('click', function() { //If clicked on the first link of #chatpanel...
	    if(jQuery(this).next(".subpanel").is(':visible')){ //If subpanel is already active...
	        jQuery(this).next(".subpanel").hide(); //Hide active subpanel
	        jQuery("#drupalchat li a").removeClass('active'); //Remove active class on the subpanel trigger
	    }
	    else { //if subpanel is not active...
	        jQuery(".subpanel").hide(); //Hide all subpanels
	        jQuery(this).next(".subpanel").toggle(); //Toggle the subpanel to make active
	        jQuery("#drupalchat li a").removeClass('active'); //Remove active class on all subpanel trigger
	        jQuery(this).toggleClass('active'); //Toggle the active class on the subpanel trigger
	        // Chat box functions
	        var isTextarea = jQuery(this).next(".subpanel").children(".chatboxinput").children(".chatboxtextarea");
	        if (isTextarea.length > 0) { 
	        	isTextarea[0].focus();
	        	jQuery(this).next(".subpanel").children(".chatboxcontent").scrollTop(jQuery(this).next(".subpanel").children(".chatboxcontent")[0].scrollHeight);
	        }
	    }
	    return false; //Prevent browser jump to link anchor
	});
		
	jQuery('.subpanel .subpanel_title').live('click', function() { //Click anywhere and...
	    jQuery(".subpanel").hide(); //hide subpanel
	    jQuery("#drupalchat li a").removeClass('active'); //remove active class on subpanel trigger
	});	
	jQuery('.subpanel ul').click(function(e) {
//	    e.stopPropagation(); //Prevents the subpanel ul from closing on click
	});

	jQuery("#chatpanel .subpanel li:not(.link) a").live('click', function() {
            chatWith(jQuery(this).attr("class"), jQuery(this).text());
	});	
	
	jQuery(".chatbox .subpanel_title span:not(.min)").live('click', function () {
		closeChatBox(jQuery(this).attr('class'));
	});
	//alert(Drupal.settings.drupalchat.status);
	if (Drupal.settings.drupalchat.status == 1) {
            jQuery(".chat_options .chat_loading").removeClass('chat_loading').addClass('status-2').html(Drupal.settings.drupalchat.goIdle);
            jQuery("#chatpanel .icon").attr("src", Drupal.settings.drupalchat.images + "chat-1.png");
	}
	else if (Drupal.settings.drupalchat.status == 2) {
            jQuery(".chat_options .chat_loading").removeClass('chat_loading').addClass('status-1').html(Drupal.settings.drupalchat.goOnline);
            jQuery("#chatpanel .icon").attr("src", Drupal.settings.drupalchat.images + "chat-2.png");
	}
		
	jQuery(".chat_options .status-1").live('click', function() {
            jQuery(".chat_options .status-1").removeClass('status-1').addClass('chat_loading');
            jQuery.post(Drupal.settings.drupalchat.statusUrl, {status: "1"}, function(data){
                jQuery(".chat_options .chat_loading").removeClass('chat_loading').addClass('status-2').html(Drupal.settings.drupalchat.goIdle);
                jQuery("#chatpanel .icon").attr("src", Drupal.settings.drupalchat.images + "chat-1.png");
            });
	});
	jQuery(".chat_options .status-2").live('click', function() {
            jQuery(".chat_options .status-2").removeClass('status-2').addClass('chat_loading');
            jQuery.post(Drupal.settings.drupalchat.statusUrl, {status: "2"}, function(data){
                jQuery(".chat_options .chat_loading").removeClass('chat_loading').addClass('status-1').html(Drupal.settings.drupalchat.goOnline);
                jQuery("#chatpanel .icon").attr("src", Drupal.settings.drupalchat.images + "chat-2.png");
            });
	});	
	
	jQuery(".chat_options .options").live('click', function() {
            alert('Under Construction');
	});
  
	// Add short delay before first poll call. This avoids Chrome loading-icon bug. 
  setTimeout(function () {
    chatPoll();
  }, 500);
});


function chatWith(chatboxtitle, chatboxname) {
    createChatBox(chatboxtitle, chatboxname);
    jQuery("#chatbox_"+chatboxtitle+" a:first").click(); //Toggle the subpanel to make active
    jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
}


function createChatBox(chatboxtitle, chatboxname, chatboxblink) {
    if (jQuery("#chatbox_"+chatboxtitle).length > 0) {
        if (jQuery("#chatbox_"+chatboxtitle).css('display') == 'none') {
            jQuery("#chatbox_"+chatboxtitle).css('display', 'block');
        }
        jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
        return;
    }

    jQuery(" <li />" ).attr("id","chatbox_"+chatboxtitle)
    .addClass("chatbox")
    .html('<a href="#" class="chatboxhead"><span class="subpanel_title_text">'+chatboxname+'</span></a><div class="subpanel"><div class="subpanel_title"><span class="'+chatboxtitle+'">x</span><span class="min">_</span>'+chatboxname+'</div><div class="chatboxcontent"></div><div class="chatboxinput"><textarea class="chatboxtextarea" onkeydown="return checkChatBoxInputKey(event,this,\''+chatboxtitle+'\');"></textarea></div></div>')
    .prependTo(jQuery( "#mainpanel" ));

    if (chatboxblink == 1) {
        jQuery('#chatbox_'+chatboxtitle+' .chatboxhead').addClass("chatboxblink");
    }

    jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").blur(function(){
        jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").removeClass('chatboxtextareaselected');
    }).focus(function(){
        jQuery('#chatbox_'+chatboxtitle+' .chatboxhead').removeClass('chatboxblink');
        jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").addClass('chatboxtextareaselected');
    });

    jQuery("#chatbox_"+chatboxtitle).click(function() {
        if (jQuery('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') != 'none') {
            jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
        }
    });

    jQuery("#chatbox_"+chatboxtitle).show();
}

function chatPoll() {
    if(Drupal.settings.drupalchat.polling_method == '0') {
        setInterval(function() {
            jQuery.getJSON(Drupal.settings.drupalchat.pollUrl, { drupalchat_last_timestamp: drupalchat.last_timestamp }, function(data) {
                processChatData(data);
            });
            /*jQuery.post(Drupal.settings.drupalchat.pollUrl,
                {
                    drupalchat_last_timestamp: drupalchat.last_timestamp
                },
                processChatData
            );*/
        }, (Drupal.settings.drupalchat.refresh_rate) * 1000);
    }
    else if(Drupal.settings.drupalchat.polling_method == '1') {
        jQuery.getJSON(Drupal.settings.drupalchat.pollUrl, { drupalchat_last_timestamp: drupalchat.last_timestamp }, function(data) {
            processChatData(data);
        });
        /*jQuery.post(Drupal.settings.drupalchat.pollUrl,
            {
                drupalchat_last_timestamp: drupalchat.last_timestamp
            },
            processChatData
        );*/
    }
}

function processChatData(data) {
    var drupalchat_messages = data;
	//var drupalchat_messages = jQuery.getJSON(data);
        if((!drupalchat_messages.status || drupalchat_messages.status == 0)) {
		if (drupalchat_messages.messages.length > 0) {
			// Play new message sound effect
			var obj = swfobject.getObjectById("drupalchatbeep");
			if (obj && Drupal.settings.drupalchat.notificationSound == "1") {
			  try {
			    obj.drupalchatbeep(); // e.g. an external interface call
		      }
			  catch(e) {
			  }
			}			
		}
		jQuery.each(drupalchat_messages.messages, function(index, value) {
			//Add div if required.
			chatboxtitle = value.uid1;
			
			if (jQuery("#chatbox_"+chatboxtitle).length <= 0) {
				createChatBox(chatboxtitle, value.name, 1);
			}
			else if (jQuery("#chatbox_"+chatboxtitle+" .subpanel").is(':hidden')) {
				if (jQuery("#chatbox_"+chatboxtitle).css('display') == 'none') {
					jQuery("#chatbox_"+chatboxtitle).css('display','block');
				}
				jQuery("#chatbox_"+chatboxtitle+" a:first").click(); //Toggle the subpanel to make active
				jQuery("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
			}			
			value.message = value.message.replace(/{{drupalchat_newline}}/g,"<br />");
			value.message = emotify(value.message);
			if (jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent .chatboxusername a:last").html() == value.name) {
				jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<p>'+value.message+'</p>');
			}
			else {
				var currentTime = new Date();
				var hours = currentTime.getHours();
				var minutes = currentTime.getMinutes();
				if (hours < 10) {
					hours = "0" + hours;
				}
				if (minutes < 10) {
					minutes = "0" + minutes;
				}				
				jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxusername"><span class="chatboxtime">'+hours+':'+minutes+'</span><a href="'+Drupal.settings.basePath+'user/'+chatboxtitle+'">'+value.name+'</a></div><p>'+value.message+'</p>');
			}
			jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop(jQuery("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			
                        jQuery.titleAlert(Drupal.settings.drupalchat.newMessage, {requireBlur:true, stopOnFocus:true, interval:800});
		});
		
	  jQuery('#chatpanel .subpanel ul').empty();
	  jQuery.each(drupalchat_messages.buddylist, function(key, value) {
		  if (key != 'total') {
			  if (key != Drupal.settings.drupalchat.uid) {
			  	jQuery('#chatpanel .subpanel ul').append('<li class="status-' + value.status + '"><a class="' + key + '" href="#">' + value.name + '</a></li>');
			  }
		  }
		  else {
			  jQuery('#chatpanel .online-count').html(value);
		  }
		});
	  jQuery('#chatpanel .subpanel ul li:last-child').addClass('last');
	  
	  if (jQuery('#chatpanel .subpanel ul li').length <= 0) {
	  	jQuery('#chatpanel .subpanel ul').append(Drupal.settings.drupalchat.noUsers);
	  }
	  
	  //Update Timestamp.
	  drupalchat.last_timestamp = drupalchat_messages.last_timestamp;  
	}
	if (Drupal.settings.drupalchat.polling_method != '0') {
		chatPoll();
	}
}

function closeChatBox(chatboxtitle) {
	jQuery('#chatbox_'+chatboxtitle).css('display','none');
}


	

jQuery(window).unload(function(){
	YUI().use('gallery-storage-lite', function (Y) {
	    Y.StorageLite.on('storage-lite:ready', function () {
	    	Y.StorageLite.setItem('username', drupalchat.username);
	    	Y.StorageLite.setItem('uid', drupalchat.uid);
	    	Y.StorageLite.setItem('send_current_message', drupalchat.send_current_message);
	    	Y.StorageLite.setItem('last_timestamp', drupalchat.last_timestamp);
	    	Y.StorageLite.setItem('send_current_uid2', drupalchat.send_current_uid2);
	    	Y.StorageLite.setItem('attach_messages_in_queue', drupalchat.attach_messages_in_queue);
	    	Y.StorageLite.setItem('running', drupalchat.running);
	    	//alert(jQuery('#drupalchat').html());
	    	Y.StorageLite.setItem('drupalchat', jQuery('#drupalchat').html());
	        });
	    });

	

});
function drupalchat_getCookie(c_name)
{
var i,x,y,ARRcookies=document.cookie.split(";");
for (i=0;i<ARRcookies.length;i++)
  {
  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  x=x.replace(/^\s+|\s+$/g,"");
  if (x==c_name)
    {
    return unescape(y);
    }
  }
}
function drupalchat_setCookie(c_name,value,exdays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate() + exdays);
var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
document.cookie=c_name + "=" + c_value;
}
//})(jQuery);
