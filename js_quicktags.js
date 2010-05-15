// JS QuickTags version 1.3.1
//
// Copyright (c) 2002-2008 Alex King
// http://alexking.org/projects/js-quicktags
//
// Thanks to Greg Heo <greg@node79.com> for his changes 
// to support multiple toolbars per page.
//
// Licensed under the LGPL license
// http://www.gnu.org/copyleft/lesser.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// **********************************************************************
//
// This JavaScript will insert the tags below at the cursor position in IE and 
// Gecko-based browsers (Mozilla, Camino, Firefox, Netscape). For browsers that 
// do not support inserting at the cursor position (older versions of Safari, 
// OmniWeb) it appends the tags to the end of the content.
//
// Pass the ID of the <textarea> element to the edToolbar and function.
//
// Example:
//
//  <script type="text/javascript">edToolbar('canvas');</script>
//  <textarea id="canvas" rows="20" cols="50"></textarea>
//

var edButtons = new Array();
var edLinks = new Array();
var edOpenTags = new Array();

function edButton(id, display, tagStart, tagEnd, access, open) {
   this.id = id;              // used to name the toolbar button
   this.display = display;    // label on button
   this.tagStart = tagStart;  // open tag
   this.tagEnd = tagEnd;      // close tag
   this.access = access;      // set to -1 if tag does not need to be closed
   this.open = open;          // set to -1 if tag does not need to be closed
}

edButtons.push(
   new edButton(
      'ed_p'
      ,'P'
      ,'<p>'
      ,'</p>\n'
      ,'p'
   )
);

edButtons.push(
   new edButton(
      'ed_bold'
      ,'B'
      ,'<b>'
      ,'</b>'
      ,'b'
   )
);

edButtons.push(
   new edButton(
      'ed_italic'
      ,'I'
      ,'<i>'
      ,'</i>'
      ,'i'
   )
);

edButtons.push(
   new edButton(
      'ed_link'
      ,'URL'
      ,''
      ,''
      ,'a'
   )
); // special case

edButtons.push(
   new edButton(
      'ed_img'
      ,'IMG'
      ,''
      ,''
      ,'m'
      ,-1
   )
); // special case

edButtons.push(
   new edButton(
      'ed_ul'
      ,'UL'
      ,'<ul class="contentlist">\n'
      ,'</ul>\n'
      ,'u'
   )
);

edButtons.push(
   new edButton(
      'ed_li'
      ,'LI'
      ,'\t<li>'
      ,'</li>\n'
      ,'l'
   )
);

edButtons.push(
   new edButton(
      'ed_h3'
      ,'H3'
      ,'<h3>'
      ,'</h3>\n'
      ,'3'
   )
);

edButtons.push(
   new edButton(
      'ed_bold'
      ,'BR'
      ,'<br />'
      ,''
      ,''
   )
);

edButtons.push(
   new edButton(
      'ed_code'
      ,'Code'
      ,'<pre class="command">'
      ,'</pre>\n'
      ,'c'
   )
);

edButtons.push(
   new edButton(
      'ed_quote'
      ,'Quote'
      ,'<p class="qoute">'
      ,'</p>\n'
      ,'q'
   )
);

edButtons.push(
   new edButton(
      'ed_notice'
      ,'Note'
      ,'<p class="notice">'
      ,'</p>\n'
      ,'n'
   )
);

var extendedStart = edButtons.length;

// below here are the extended buttons

function edShowButton(which, button, i) {
   if (button.access) {
      var accesskey = ' accesskey = "' + button.access + '"'
   }
   else {
      var accesskey = '';
   }
   switch (button.id) {
      case 'ed_img':
         document.write('<input type="button" id="' + button.id + '_' + which + '" ' + accesskey + ' class="ed_button" onclick="edInsertImage(\'' + which + '\');" value="' + button.display + '" />');
         break;     
      case 'ed_link':
         document.write('<input type="button" id="' + button.id + '_' + which + '" ' + accesskey + ' class="ed_button" onclick="edInsertLink(\'' + which + '\', ' + i + ');" value="' + button.display + '" />');
         break;
      default:
         document.write('<input type="button" id="' + button.id + '_' + which + '" ' + accesskey + ' class="ed_button" onclick="edInsertTag(\'' + which + '\', ' + i + ');" value="' + button.display + '"  />');
         break;
   }
}

function edAddTag(which, button) {
   if (edButtons[button].tagEnd != '') {
      edOpenTags[which][edOpenTags[which].length] = button;
      document.getElementById(edButtons[button].id + '_' + which).value = '/' + document.getElementById(edButtons[button].id + '_' + which).value;
   }
}

function edRemoveTag(which, button) {
   for (i = 0; i < edOpenTags[which].length; i++) {
      if (edOpenTags[which][i] == button) {
         edOpenTags[which].splice(i, 1);
         document.getElementById(edButtons[button].id + '_' + which).value = document.getElementById(edButtons[button].id + '_' + which).value.replace('/', '');
      }
   }
}

function edCheckOpenTags(which, button) {
   var tag = 0;
   for (i = 0; i < edOpenTags[which].length; i++) {
      if (edOpenTags[which][i] == button) {
         tag++;
      }
   }
   if (tag > 0) {
      return true; // tag found
   }
   else {
      return false; // tag not found
   }
}  

function edCloseAllTags(which) {
   var count = edOpenTags[which].length;
   for (o = 0; o < count; o++) {
      edInsertTag(which, edOpenTags[which][edOpenTags[which].length - 1]);
   }
}

function edToolbar(which) {
   document.write('<div id="ed_toolbar_' + which + '"><span>');
   for (i = 0; i < extendedStart; i++) {
      edShowButton(which, edButtons[i], i);
   }
   if (edShowExtraCookie()) {
      document.write(
         '<input type="button" id="ed_close_' + which + '" class="ed_button" onclick="edCloseAllTags(\'' + which + '\');" value="Close Tags" />'
         + '<input type="button" id="ed_spell_' + which + '" class="ed_button" onclick="edSpell(\'' + which + '\');" value="Dict" />'
         + '<input type="button" id="ed_extra_show_' + which + '" class="ed_button" onclick="edShowExtra(\'' + which + '\')" value="&raquo;" style="visibility: hidden;" />'
         + '</span><br />'
         + '<span id="ed_extra_buttons_' + which + '">'
         + '<input type="button" id="ed_extra_hide_' + which + '" class="ed_button" onclick="edHideExtra(\'' + which + '\');" value="&laquo;" />'
      );
   }
   else {
      document.write(
         '<input type="button" id="ed_close_' + which + '" class="ed_button" onclick="edCloseAllTags(\'' + which + '\');" value="Затвори таговете" />'
         + '</span><br />'
         + '<span id="ed_extra_buttons_' + which + '" style="display: none;">'
         + '<input type="button" id="ed_extra_hide_' + which + '" class="ed_button" onclick="edHideExtra(\'' + which + '\');" value="&laquo;" />'
      );
   }

   document.write('</span>');
   document.write('</div>');
   edOpenTags[which] = new Array();
}

// insertion code

function edInsertTag(which, i) {
    myField = document.getElementById(which);
   //IE support
   if (document.selection) {
      myField.focus();
       sel = document.selection.createRange();
      if (sel.text.length > 0) {
         sel.text = edButtons[i].tagStart + sel.text + edButtons[i].tagEnd;
      }
      else {
         if (!edCheckOpenTags(which, i) || edButtons[i].tagEnd == '') {
            sel.text = edButtons[i].tagStart;
            edAddTag(which, i);
         }
         else {
            sel.text = edButtons[i].tagEnd;
            edRemoveTag(which, i);
         }
      }
      myField.focus();
   }
   //MOZILLA/NETSCAPE support
   else if (myField.selectionStart || myField.selectionStart == '0') {
      var startPos = myField.selectionStart;
      var endPos = myField.selectionEnd;
      var cursorPos = endPos;
      var scrollTop = myField.scrollTop;
      if (startPos != endPos) {
         myField.value = myField.value.substring(0, startPos)
                       + edButtons[i].tagStart
                       + myField.value.substring(startPos, endPos) 
                       + edButtons[i].tagEnd
                       + myField.value.substring(endPos, myField.value.length);
         cursorPos += edButtons[i].tagStart.length + edButtons[i].tagEnd.length;
      }
      else {
         if (!edCheckOpenTags(which, i) || edButtons[i].tagEnd == '') {
            myField.value = myField.value.substring(0, startPos) 
                          + edButtons[i].tagStart
                          + myField.value.substring(endPos, myField.value.length);
            edAddTag(which, i);
            cursorPos = startPos + edButtons[i].tagStart.length;
         }
         else {
            myField.value = myField.value.substring(0, startPos) 
                          + edButtons[i].tagEnd
                          + myField.value.substring(endPos, myField.value.length);
            edRemoveTag(which, i);
            cursorPos = startPos + edButtons[i].tagEnd.length;
         }
      }
      myField.focus();
      myField.selectionStart = cursorPos;
      myField.selectionEnd = cursorPos;
      myField.scrollTop = scrollTop;
   }
   else {
      if (!edCheckOpenTags(which, i) || edButtons[i].tagEnd == '') {
         myField.value += edButtons[i].tagStart;
         edAddTag(which, i);
      }
      else {
         myField.value += edButtons[i].tagEnd;
         edRemoveTag(which, i);
      }
      myField.focus();
   }
}

function edInsertContent(which, myValue) {
    myField = document.getElementById(which);
   //IE support
   if (document.selection) {
      myField.focus();
      sel = document.selection.createRange();
      sel.text = myValue;
      myField.focus();
   }
   //MOZILLA/NETSCAPE support
   else if (myField.selectionStart || myField.selectionStart == '0') {
      var startPos = myField.selectionStart;
      var endPos = myField.selectionEnd;
      var scrollTop = myField.scrollTop;
      myField.value = myField.value.substring(0, startPos)
                    + myValue 
                      + myField.value.substring(endPos, myField.value.length);
      myField.focus();
      myField.selectionStart = startPos + myValue.length;
      myField.selectionEnd = startPos + myValue.length;
      myField.scrollTop = scrollTop;
   } else {
      myField.value += myValue;
      myField.focus();
   }
}

function edInsertLink(which, i, defaultValue) {
    myField = document.getElementById(which);
   if (!defaultValue) {
      defaultValue = 'http://';
   }
   if (!edCheckOpenTags(which, i)) {
      var URL = prompt('Въведете URL адреса' ,defaultValue);
      var NAME = prompt('Въведете името на връзката', defaultValue);
      if (URL && NAME) {
         edButtons[i].tagStart = '<a href="' + URL + '">' + NAME + '</a>';
         edInsertTag(which, i);
      }
   }
   else {
      edInsertTag(which, i);
   }
}

function edInsertImage(which) {
   myField = document.getElementById(which);
   var myValue, myDesc, i, numImgs; 
   var divSection = '\n<div align="center">\n<table border="0" cellpadding="0" cellspacing="6">\n<tr>\n'
   
   i = numImgs = 0;
   while ((myValue = prompt('Въведете адреса на изображението, ESC за край', 'http://'))) {
      
      if (numImgs == 3) {
         edInsertContent (which, '\n</tr><tr>');
         numImgs = 0;
      }
      numImgs++;
      
      i++;
      if (i == 1)
         edInsertContent(which, divSection);
         
      myDesc = prompt('Въведете описание на изображението', '');
      myValue = '<td>\n<table class="imgitem">\n\t<tr>\n\t<td>\n'
            + '<a href="' + myValue + '" target="blank"><img src="' 
            + myValue + '" width="150" height="150" border="0"'
            + '" alt="' +  myDesc + '" title="' + myDesc
            + '" /></a>\n\t</td>\n\t</tr>\n\t<tr>\n<td class="imgdesc">'
            + myDesc + '</td>\n</tr>\n</table></td>';
      edInsertContent(which, myValue);
      
      
   }
   if (i)
      edInsertContent (which, '\n</tr>\n</table></div>');
}

function edShowExtraCookie() {
   var cookies = document.cookie.split(';');
   for (var i=0;i < cookies.length; i++) {
      var cookieData = cookies[i];
      while (cookieData.charAt(0) == ' ') {
         cookieData = cookieData.substring(1, cookieData.length);
      }
      if (cookieData.indexOf('js_quicktags_extra') == 0) {
         if (cookieData.substring(19, cookieData.length) == 'show') {
            return true;
         }
         else {
            return false;
         }
      }
   }
   return false;
}
