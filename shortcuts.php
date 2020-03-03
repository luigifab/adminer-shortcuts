<?php
/**
 * Created W/25/10/2017
 * Updated J/30/01/2020
 *
 * Copyright 2017-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

	public const VERSION = '1.4.0';

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
#shortcuts { padding:0.8em 1em 0; font-size:0.85em; line-height:1.2em; }
#shortcutsClear { position:absolute; right: 0.2em; margin-top:0.4em; line-height:1.6em; border:0; background:none; cursor:pointer; }
#shortcutsField { margin-top:0.4em; padding:0.2em; width:100%; border:1px solid gray; }
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
window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(t,e,s){for(e=e||window,s=0;s<this.length;s++)t.call(e,this[s],s,this)});var shortcuts=new function(){"use strict";this.start=function(){var e,t,s=document.getElementById("shortcutsField");s&&(s.addEventListener("input",shortcuts.filter),s.addEventListener("keydown",shortcuts.history),s.removeEventListener("focus",inputFocus),s.removeEventListener("blur",inputBlur),s.parentNode.removeAttribute("style"),(t=this.storage("shortcuts_"+shortcuts.dbname))&&(s.value=t,shortcuts.filter(t)),e=document.getElementById("shortcutsHistory"),(t=this.storage("shortcutsHistory"))&&("#"!==t.charAt(0)&&(t=t.replace(/\|/g,"#")),t.slice(1,-1).split("#").reverse().forEach(function(t){(s=document.createElement("li")).addEventListener("click",shortcuts.history),s.appendChild(document.createTextNode(t)),e.appendChild(s)})),document.getElementById("shortcutsClear").addEventListener("click",shortcuts.clear)),1===(s=document.querySelectorAll('#fieldset-search select[name*="[op]"]')).length&&(s[0].value="LIKE %%")},this.filter=function(t){var e,s,o,r,l,i,n="string"==typeof t?t:t.target.value;document.querySelectorAll("#tables a.structure, #tables a.view, #tables-views + form tbody th a[id][title]").forEach(function(t){r=[],0<(e=n.toLowerCase().trim()).length?(e=e.split(" "),l=e.length,o=t.textContent.toLowerCase().trim(),i=0,e.forEach(function(t){if("-"===t||"|"===t)l--;else if("-"===t.charAt(0))l--,-1<o.indexOf(t.substr(1))&&(l=-1);else if(-1<t.indexOf("|")){for(s=t.split("|");0<s.length;)if(0<(t=s.pop()).length&&-1<o.indexOf(t)){i++;break}}else-1<o.indexOf(t)&&i++}),r.push(i===l)):r.push(!0),t.hasAttribute("id")?t.parentNode.parentNode.setAttribute("style",-1<r.indexOf(!1)?"display:none;":""):t.parentNode.setAttribute("style",-1<r.indexOf(!1)?"display:none;":"display:block;")}),shortcuts.storage("shortcuts_"+shortcuts.dbname,n)},this.history=function(t){var e=document.getElementById("shortcutsHistory").querySelector("li.foc"),s=document.getElementById("shortcutsField"),o=shortcuts.storage("shortcutsHistory"),r=!1;if("click"===t.type&&(s.focus(),e&&e.removeAttribute("class"),(e=t.target).setAttribute("class","foc"),r=!0),r||13===t.keyCode){if(e)e.removeAttribute("class"),o=s.value=e.textContent,s.setSelectionRange(o.length,o.length),shortcuts.filter(o);else if((e=document.getElementById("tables").querySelector('li[style="display: block;"] a.select'))?console.log("shortcuts 13a/enter = select first result and update history"):(e=document.getElementById("tables").querySelector('li[style="display:block;"] a.select'))?console.log("shortcuts 13b/enter = select first result and update history"):(e=document.getElementById("tables").querySelector('li[style="display:block"] a.select'))&&console.log("shortcuts 13c/enter = select first result and update history"),e){for("string"==typeof o&&1<o.length?o.indexOf("#"+s.value+"#")<0&&(o=o+s.value+"#"):o="#"+s.value+"#",o=o.replace(/##/g,"#");11<o.match(/#/g).length;)o=o.substring(o.indexOf("#",2));shortcuts.storage("shortcutsHistory",o),e.click()}t.preventDefault()}else 46===t.keyCode?e&&(console.log("shortcuts 46/suppr = remove history"),e.parentNode.removeChild(e),o=o.replace("#"+e.textContent+"#","#"),shortcuts.storage("shortcutsHistory",o),t.preventDefault()):38===t.keyCode?(console.log("shortcuts 38/top = move history"),shortcuts.move(!1),t.preventDefault()):40===t.keyCode?(console.log("shortcuts 40/bottom = move history"),shortcuts.move(!0),t.preventDefault()):e&&e.removeAttribute("class")},this.move=function(t){var e,s=document.getElementById("shortcutsHistory");(e=s.querySelector("li.foc"))?(e.removeAttribute("class"),(e=t?e.nextSibling:e.previousSibling)&&e.setAttribute("class","foc")):(e=s.querySelector("li"))&&e.setAttribute("class","foc")},this.clear=function(){var t=document.getElementById("shortcutsField");t.value="",shortcuts.filter(""),t.focus(),(t=document.getElementById("shortcutsHistory").querySelector("li.foc"))&&t.removeAttribute("class")},this.unload=function(){this.storage("shortcutsHistory",this.storage("shortcutsHistory"))},this.storage=function(t,e){if(null===e)localStorage.removeItem(t),sessionStorage.removeItem(t);else{if(void 0===e)return localStorage.getItem(t)||sessionStorage.getItem(t);localStorage.setItem(t,e),sessionStorage.setItem(t,e)}}};"function"==typeof self.addEventListener&&(self.addEventListener("load",shortcuts.start.bind(shortcuts)),self.addEventListener("beforeunload",shortcuts.unload.bind(shortcuts)));
CODE;

		//echo file_get_contents('./app.js');
		echo "\n",'shortcuts.dbname = "',$_GET['db'],'";';
		echo '</script>';
	}
}