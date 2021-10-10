<?php
/**
 * Created W/25/10/2017
 * Updated M/28/09/2021
 *
 * Copyright 2017-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/adminer/shortcuts
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Shortcuts {

	public const VERSION = '1.6.1';

	public function selectOrderPrint(&$order, $columns, $indexes) {

		if (!empty($_GET['select']) && empty($_GET['order'])) {

			foreach ($indexes as $index) {
				if (!empty($index['columns']) && !empty($index['type']) && ($index['type'] == 'PRIMARY')) {
					$field = $index['columns'][0];
					break;
				}
			}

			if (empty($field) && !empty($columns))
				$field = $columns[0];

			if (!empty($field) && empty($order)) {
				$order = ['`'.$field.'` DESC'];
				$_GET['order'][0] = $field;
				$_GET['desc'][0] = 1;
				$this->_superOrder = $order;
			}
		}
	}

	public function selectQueryBuild($select, $where, $group, $order, $limit, $page) {

		if (!empty($_GET['select']) && empty($order) && !empty($this->_superOrder)) {

			global $jush;
			$is_group = (count($group) < count($select));
			$order = $this->_superOrder;

			return 'SELECT' . limit(
				($_GET['page'] != 'last' && $limit != '' && $group && $is_group && $jush == 'sql' ? 'SQL_CALC_FOUND_ROWS ' : '') . implode(', ', $select) . "\nFROM " . table($_GET['select']),
				($where ? "\nWHERE " . implode(' AND ', $where) : '') . ($group && $is_group ? "\nGROUP BY " . implode(', ', $group) : '') . ($order ? "\nORDER BY " . implode(', ', $order) : ''),
				($limit != '' ? +$limit : null),
				($page ? $limit * $page : 0),
				"\n"
			);
		}

		return '';
	}

	public function tablesPrint($tables) {

		if (empty($_GET['db']))
			return;

		// syntaxe Nowdoc
		echo <<<HTML
<style>
#shortcuts { position:relative; margin:1.2em 1em 0; font-size:0.85em; line-height:1.2em; }
#shortcutsField { display:block; padding:0.2em; width:calc(100% - 0.4em - 2px); border:1px solid gray; }
#shortcutsClear { position:absolute; top:1px; right:0; line-height:1.55em; border:0; background:none; cursor:pointer; }
#shortcuts ul { margin:0.3em 0 0; padding:0.2em 0.2em 0; list-style:none; }
#shortcuts li { line-height:130%; cursor:pointer; }
#shortcuts li:hover, #shortcutsField:focus ~ ul li.foc { text-decoration:underline; }
</style>
<div style="visibility:hidden;" id="shortcuts">
<button type="button" id="shortcutsClear">x</button>
<input autocapitalize="off" autocorrect="off" spellcheck="false" autocomplete="off" id="shortcutsField">
<ul id="shortcutsHistory"></ul>
</div>
HTML;

		// syntaxe Nowdoc
		echo function_exists('nonce') ? '<script '.nonce().'>' : '<script>';
		echo <<<'CODE'
window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(t,e,s){for(e=e||window,s=0;s<this.length;s++)t.call(e,this[s],s,this)});var shortcuts=new function(){"use strict";this.init=function(){console.info("shortcuts.app - hello");var e,t,s=document.getElementById("shortcutsField");s&&(s.addEventListener("input",shortcuts.filter),s.addEventListener("keydown",shortcuts.history),s.removeEventListener("focus",inputFocus),s.removeEventListener("blur",inputBlur),s.parentNode.removeAttribute("style"),(t=this.storage("shortcuts_"+shortcuts.dbname))&&(s.value=t,shortcuts.filter(t)),e=document.getElementById("shortcutsHistory"),(t=this.storage("shortcutsHistory"))&&(t="#"!==t.charAt(0)?t.replace(/\|/g,"#"):t).slice(1,-1).split("#").reverse().forEach(function(t){(s=document.createElement("li")).addEventListener("click",shortcuts.history),s.appendChild(document.createTextNode(t)),e.appendChild(s)}),document.getElementById("shortcutsClear").addEventListener("click",shortcuts.clear)),(s=document.getElementById("shortcutsEditField"))&&(s.addEventListener("input",shortcuts.filter),s.removeEventListener("focus",inputFocus),s.removeEventListener("blur",inputBlur),s.parentNode.removeAttribute("style"),document.getElementById("shortcutsEditClear").addEventListener("click",shortcuts.clear)),1===(s=document.querySelectorAll('#fieldset-search select[name*="[op]"]')).length&&(s[0].value="LIKE %%")},this.filter=function(t){var e,s,o,r,i,l,n="string"==typeof t?t:t.target.value,c=!0,t=(c="object"==typeof t&&0<t.target.getAttribute("id").indexOf("Edit")?!1:c)?"#tables a.structure, #tables a.view, #tables-views + form tbody th a[id][title]":"#form table.layout th span[title]";document.querySelectorAll(t).forEach(function(t){r=[],0<(e=n.toLowerCase().trim()).length?(e=e.split(" "),i=e.length,o=t.textContent.toLowerCase().trim(),l=0,e.forEach(function(t){if("-"===t||"|"===t)i--;else if("-"===t.charAt(0))i--,-1<o.indexOf(t.substr(1))&&(i=-1);else if(-1<t.indexOf("|"))for(s=t.split("|");0<s.length;)0<(t=s.pop()).length&&-1<o.indexOf(t)&&(s="",l++);else-1<o.indexOf(t)&&l++}),r.push(l===i)):r.push(!0),!c||t.hasAttribute("id")?(t.parentNode.parentNode.setAttribute("style",-1<r.indexOf(!1)?"display:none;":""),(s=t.parentNode.parentNode.querySelector("input"))&&t.hasAttribute("id")&&(-1<r.indexOf(!1)?s.removeAttribute("name"):s.setAttribute("name","tables[]"))):t.parentNode.setAttribute("style",-1<r.indexOf(!1)?"display:none;":"display:block;")}),c&&shortcuts.storage("shortcuts_"+shortcuts.dbname,n)},this.history=function(t){var e=document.getElementById("shortcutsHistory").querySelector("li.foc"),s=document.getElementById("shortcutsField"),o=shortcuts.storage("shortcutsHistory"),r=!1;if("click"===t.type&&(s.focus(),e&&e.removeAttribute("class"),(e=t.target).setAttribute("class","foc"),r=!0),r||13===t.keyCode){if(e)e.removeAttribute("class"),o=s.value=e.textContent,s.setSelectionRange(o.length,o.length),shortcuts.filter(o);else if((e=document.getElementById("tables").querySelector('li[style="display: block;"] a.select'))?console.log("shortcuts 13a/enter = select first result and update history"):(e=document.getElementById("tables").querySelector('li[style="display:block;"] a.select'))?console.log("shortcuts 13b/enter = select first result and update history"):(e=document.getElementById("tables").querySelector('li[style="display:block"] a.select'))&&console.log("shortcuts 13c/enter = select first result and update history"),e){for("string"==typeof o&&1<o.length?o.indexOf("#"+s.value+"#")<0&&(o=o+s.value+"#"):o="#"+s.value+"#",o=o.replace(/##/g,"#");11<o.match(/#/g).length;)o=o.substring(o.indexOf("#",2));shortcuts.storage("shortcutsHistory",o),e.click()}t.preventDefault()}else 46===t.keyCode?e&&(console.log("shortcuts 46/suppr = remove history"),e.remove(),o=o.replace("#"+e.textContent+"#","#"),shortcuts.storage("shortcutsHistory",o),t.preventDefault()):38===t.keyCode?(console.log("shortcuts 38/top = move history"),shortcuts.move(!1),t.preventDefault()):40===t.keyCode?(console.log("shortcuts 40/bottom = move history"),shortcuts.move(!0),t.preventDefault()):e&&e.removeAttribute("class")},this.move=function(t){var e,s=document.getElementById("shortcutsHistory");(e=s.querySelector("li.foc"))?(e.removeAttribute("class"),(e=t?e.nextSibling:e.previousSibling)&&e.setAttribute("class","foc")):(e=s.querySelector("li"))&&e.setAttribute("class","foc")},this.clear=function(t){t=t.target.parentNode.querySelector("input");t.value="",shortcuts.filter({target:t}),t.focus(),(t=document.getElementById("shortcutsHistory").querySelector("li.foc"))&&t.removeAttribute("class")},this.storage=function(t,e){if(null===e)localStorage.removeItem(t),sessionStorage.removeItem(t);else{if(void 0===e)return localStorage.getItem(t)||sessionStorage.getItem(t);localStorage.setItem(t,e),sessionStorage.setItem(t,e)}},this.unload=function(){this.storage("shortcutsHistory",this.storage("shortcutsHistory"))}};"function"==typeof self.addEventListener&&(self.addEventListener("load",shortcuts.init.bind(shortcuts)),self.addEventListener("beforeunload",shortcuts.unload.bind(shortcuts)));window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(t,e,s){for(e=e||window,s=0;s<this.length;s++)t.call(e,this[s],s,this)});var shortcuts=new function(){"use strict";this.init=function(){console.info("shortcuts.app - hello");var e,t,s=document.getElementById("shortcutsField");s&&(s.addEventListener("input",shortcuts.filter),s.addEventListener("keydown",shortcuts.history),s.removeEventListener("focus",inputFocus),s.removeEventListener("blur",inputBlur),s.parentNode.removeAttribute("style"),(t=this.storage("shortcuts_"+shortcuts.dbname))&&(s.value=t,shortcuts.filter(t)),e=document.getElementById("shortcutsHistory"),(t=this.storage("shortcutsHistory"))&&(t="#"!==t.charAt(0)?t.replace(/\|/g,"#"):t).slice(1,-1).split("#").reverse().forEach(function(t){(s=document.createElement("li")).addEventListener("click",shortcuts.history),s.appendChild(document.createTextNode(t)),e.appendChild(s)}),document.getElementById("shortcutsClear").addEventListener("click",shortcuts.clear)),(s=document.getElementById("shortcutsEditField"))&&(s.addEventListener("input",shortcuts.filter),s.removeEventListener("focus",inputFocus),s.removeEventListener("blur",inputBlur),s.parentNode.removeAttribute("style"),document.getElementById("shortcutsEditClear").addEventListener("click",shortcuts.clear)),1===(s=document.querySelectorAll('#fieldset-search select[name*="[op]"]')).length&&(s[0].value="LIKE %%")},this.filter=function(t){var e,s,o,r,i,l,n="string"==typeof t?t:t.target.value,c=!0,t=(c="object"==typeof t&&0<t.target.getAttribute("id").indexOf("Edit")?!1:c)?"#tables a.structure, #tables a.view, #tables-views + form tbody th a[id][title]":"#form table.layout th span[title]";document.querySelectorAll(t).forEach(function(t){r=[],0<(e=n.toLowerCase().trim()).length?(e=e.split(" "),i=e.length,o=t.textContent.toLowerCase().trim(),l=0,e.forEach(function(t){if("-"===t||"|"===t)i--;else if("-"===t.charAt(0))i--,-1<o.indexOf(t.substr(1))&&(i=-1);else if(-1<t.indexOf("|"))for(s=t.split("|");0<s.length;)0<(t=s.pop()).length&&-1<o.indexOf(t)&&(s="",l++);else-1<o.indexOf(t)&&l++}),r.push(l===i)):r.push(!0),!c||t.hasAttribute("id")?(t.parentNode.parentNode.setAttribute("style",-1<r.indexOf(!1)?"display:none;":""),(s=t.parentNode.parentNode.querySelector("input"))&&t.hasAttribute("id")&&(-1<r.indexOf(!1)?s.removeAttribute("name"):s.setAttribute("name","tables[]"))):t.parentNode.setAttribute("style",-1<r.indexOf(!1)?"display:none;":"display:block;")}),c&&shortcuts.storage("shortcuts_"+shortcuts.dbname,n)},this.history=function(t){var e=document.getElementById("shortcutsHistory").querySelector("li.foc"),s=document.getElementById("shortcutsField"),o=shortcuts.storage("shortcutsHistory"),r=!1;if("click"===t.type&&(s.focus(),e&&e.removeAttribute("class"),(e=t.target).setAttribute("class","foc"),r=!0),r||13===t.keyCode){if(e)e.removeAttribute("class"),o=s.value=e.textContent,s.setSelectionRange(o.length,o.length),shortcuts.filter(o);else if((e=document.getElementById("tables").querySelector('li[style="display: block;"] a.select'))?console.log("shortcuts 13a/enter = select first result and update history"):(e=document.getElementById("tables").querySelector('li[style="display:block;"] a.select'))?console.log("shortcuts 13b/enter = select first result and update history"):(e=document.getElementById("tables").querySelector('li[style="display:block"] a.select'))&&console.log("shortcuts 13c/enter = select first result and update history"),e){for("string"==typeof o&&1<o.length?o.indexOf("#"+s.value+"#")<0&&(o=o+s.value+"#"):o="#"+s.value+"#",o=o.replace(/##/g,"#");11<o.match(/#/g).length;)o=o.substring(o.indexOf("#",2));shortcuts.storage("shortcutsHistory",o),e.click()}t.preventDefault()}else 46===t.keyCode?e&&(console.log("shortcuts 46/suppr = remove history"),e.remove(),o=o.replace("#"+e.textContent+"#","#"),shortcuts.storage("shortcutsHistory",o),t.preventDefault()):38===t.keyCode?(console.log("shortcuts 38/top = move history"),shortcuts.move(!1),t.preventDefault()):40===t.keyCode?(console.log("shortcuts 40/bottom = move history"),shortcuts.move(!0),t.preventDefault()):e&&e.removeAttribute("class")},this.move=function(t){var e,s=document.getElementById("shortcutsHistory");(e=s.querySelector("li.foc"))?(e.removeAttribute("class"),(e=t?e.nextSibling:e.previousSibling)&&e.setAttribute("class","foc")):(e=s.querySelector("li"))&&e.setAttribute("class","foc")},this.clear=function(t){t=t.target.parentNode.querySelector("input");t.value="",shortcuts.filter({target:t}),t.focus(),(t=document.getElementById("shortcutsHistory").querySelector("li.foc"))&&t.removeAttribute("class")},this.storage=function(t,e){if(null===e)localStorage.removeItem(t),sessionStorage.removeItem(t);else{if(void 0===e)return localStorage.getItem(t)||sessionStorage.getItem(t);localStorage.setItem(t,e),sessionStorage.setItem(t,e)}},this.unload=function(){this.storage("shortcutsHistory",this.storage("shortcutsHistory"))}};"function"==typeof self.addEventListener&&(self.addEventListener("load",shortcuts.init.bind(shortcuts)),self.addEventListener("beforeunload",shortcuts.unload.bind(shortcuts)));
CODE;

		//echo file_get_contents('./app.js');
		echo "\n",'shortcuts.dbname = "',$_GET['db'],'";';
		echo '</script>';
	}

	public function editRowPrint($table, $fields, $row, $update) {

		if (empty($update))
			return;

		// syntaxe Nowdoc
		echo <<<HTML
<style>
#shortcutsEdit { position:relative; margin-right:20px; font-size:0.85em; }
#shortcutsEditField { display:block; padding:0.2em; width:calc(100% - 0.4em - 2px); border:1px solid gray; }
#shortcutsEditClear { position:absolute; top:1px; right:0; line-height:1.5em; border:0; background:none; cursor:pointer; }
</style>
<div style="visibility:hidden;" id="shortcutsEdit">
<button type="button" id="shortcutsEditClear">x</button>
<input autocapitalize="off" autocorrect="off" spellcheck="false" autocomplete="off" id="shortcutsEditField">
</div>
HTML;
	}
}