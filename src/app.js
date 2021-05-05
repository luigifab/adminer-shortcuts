/**
 * Created W/25/10/2017
 * Updated V/23/04/2021
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

if (window.NodeList && !NodeList.prototype.forEach) {
	NodeList.prototype.forEach = function (callback, that, i) {
		that = that || window;
		for (i = 0; i < this.length; i++)
			callback.call(that, this[i], i, this);
	};
}

var shortcuts = new (function () {

	"use strict";

	this.start = function () {

		var elem = document.getElementById('shortcutsField'), root, data;
		if (elem) {

			elem.addEventListener('input', shortcuts.filter);    // pour nous
			elem.addEventListener('keydown', shortcuts.history); // pour nous
			elem.removeEventListener('focus', inputFocus);       // adminer
			elem.removeEventListener('blur', inputBlur);         // adminer
			elem.parentNode.removeAttribute('style');

			data = this.storage('shortcuts_' + shortcuts.dbname);
			if (data) {
				elem.value = data;
				shortcuts.filter(data);
			}

			root = document.getElementById('shortcutsHistory');
			data = this.storage('shortcutsHistory');
			if (data) {
				if (data.charAt(0) !== '#') // avant la v1.3 le séparateur était |
					data = data.replace(/\|/g, '#');
				data.slice(1, -1).split('#').reverse().forEach(function (text) {
					elem = document.createElement('li');
					elem.addEventListener('click', shortcuts.history);
					elem.appendChild(document.createTextNode(text));
					root.appendChild(elem);
				});
			}

			document.getElementById('shortcutsClear').addEventListener('click', shortcuts.clear);
		}

		elem = document.getElementById('shortcutsEditField');
		if (elem) {
			elem.addEventListener('input', shortcuts.filter);    // pour nous
			elem.removeEventListener('focus', inputFocus);       // adminer
			elem.removeEventListener('blur', inputBlur);         // adminer
			elem.parentNode.removeAttribute('style');
			document.getElementById('shortcutsEditClear').addEventListener('click', shortcuts.clear);
		}

		elem = document.querySelectorAll('#fieldset-search select[name*="[op]"]');
		if (elem.length === 1)
			elem[0].value = 'LIKE %%';
	};

	this.filter = function (ev) {

		var query, words, tmp, text, show, size, cnt, search = (typeof ev == 'string') ? ev : ev.target.value, original = true;
		if ((typeof ev == 'object') && (ev.target.getAttribute('id').indexOf('Edit') > 0))
			original = false;

		query = original ? '#tables a.structure, #tables a.view, #tables-views + form tbody th a[id][title]' : '#form table.layout th span[title]';
		document.querySelectorAll(query).forEach(function (line) {

			show = [];

			// ...
			// words = ce qu'on cherche dans la ligne courante
			// text  = ce qu'il y a dans la ligne courante
			//...forEach(function (col, idx) {

				words = search.toLowerCase().trim(); // ce qu'on cherche

				// s'il y a des mots
				if (words.length > 0) {

					words = words.split(' ');
					size  = words.length;
					text  = line.textContent.toLowerCase().trim(); // dans quoi on cherche
					cnt   = 0;

					words.forEach(function (word) {
						if ((word === '-') || (word === '|')) {
							size--;
						}
						else if (word.charAt(0) === '-') {
							size--;
							if (text.indexOf(word.substr(1)) > -1)
								size = -1;
						}
						else if (word.indexOf('|') > -1) {
							tmp = word.split('|');
							while (tmp.length > 0) {
								word = tmp.pop();
								if ((word.length > 0) && (text.indexOf(word) > -1)) {
									tmp = '';
									cnt++;
								}
							}
						}
						else if (text.indexOf(word) > -1) {
							cnt++;
						}
					});

					show.push(cnt === size);
				}
				else {
					show.push(true);
				}
			//});

			// cache ou affiche
			if (!original || line.hasAttribute('id')) {
				line.parentNode.parentNode.setAttribute('style', (show.indexOf(false) > -1) ? 'display:none;' : '');
				// tables et vues (empèche la sélection des tables)
				tmp = line.parentNode.parentNode.querySelector('input');
				if (tmp && line.hasAttribute('id')) {
					if (show.indexOf(false) > -1)
						tmp.removeAttribute('name');
					else
						tmp.setAttribute('name', 'tables[]');
				}
			}
			else {
				line.parentNode.setAttribute('style', (show.indexOf(false) > -1) ? 'display:none;' : 'display:block;');
			}
		});

		if (original)
			shortcuts.storage('shortcuts_' + shortcuts.dbname, search);
	};

	this.history = function (ev) {

		var elem  = document.getElementById('shortcutsHistory').querySelector('li.foc'),
		    input = document.getElementById('shortcutsField'),
		    data  = shortcuts.storage('shortcutsHistory'),
		    fake  = false;

		if (ev.type === 'click') {
			input.focus();
			// marque l'entrée dans l'historique
			if (elem)
				elem.removeAttribute('class');
			elem = ev.target; // un li qui devient un li.foc
			elem.setAttribute('class', 'foc');
			// simule l'événement entrée sur le input de la recherche
			//ev.keyCode = 13; //ne fonctionne pas sur Chrome 29
			fake = true;
		}

		if (fake || (ev.keyCode === 13)) { // entrée
			if (elem) {
				// remplace le contenu du input de la recherche
				// par la valeur sélectionnée dans l'historique (elem = li.foc)
				// avant de filtrer la liste des tables
				elem.removeAttribute('class');
				data = input.value = elem.textContent;
				input.setSelectionRange(data.length, data.length);
				shortcuts.filter(data);
			}
			else {
				// recherche l'éventuel premier résultat
				// par rapport au input de la recherche
				// et donc par rapport aux tables filtrées
				if (elem = document.getElementById('tables').querySelector('li[style="display: block;"] a.select'))
					console.log('shortcuts 13a/enter = select first result and update history');
				else if (elem = document.getElementById('tables').querySelector('li[style="display:block;"] a.select'))
					console.log('shortcuts 13b/enter = select first result and update history');
				else if (elem = document.getElementById('tables').querySelector('li[style="display:block"] a.select'))
					console.log('shortcuts 13c/enter = select first result and update history');

				// mémorise la recherche, avec un maximum de 10 items
				// puis clic sur le premier résultat pour faire un select sur la table
				if (elem) {

					if ((typeof data == 'string') && (data.length > 1)) {
						if (data.indexOf('#' + input.value + '#') < 0)
							data = data + input.value + '#';
					}
					else {
						data = '#' + input.value + '#';
					}

					data = data.replace(/##/g, '#');
					while (data.match(/#/g).length > 11)
						data = data.substring(data.indexOf('#', 2));

					shortcuts.storage('shortcutsHistory', data);
					elem.click(); // elem = un li[display=block]
				}
			}
			ev.preventDefault();
		}
		else if (ev.keyCode === 46) { // suppr
			if (elem) {

				console.log('shortcuts 46/suppr = remove history');
				elem.parentNode.removeChild(elem);

				data = data.replace('#' + elem.textContent + '#', '#');
				shortcuts.storage('shortcutsHistory', data);

				ev.preventDefault();
			}
		}
		else if (ev.keyCode === 38) { // haut
			console.log('shortcuts 38/top = move history');
			shortcuts.move(false);
			ev.preventDefault();
		}
		else if (ev.keyCode === 40) { // bas
			console.log('shortcuts 40/bottom = move history');
			shortcuts.move(true);
			ev.preventDefault();
		}
		else if (elem) {
			elem.removeAttribute('class');
		}
	};

	this.move = function (next) {

		var root = document.getElementById('shortcutsHistory'), elem;
		if (elem = root.querySelector('li.foc')) {

			elem.removeAttribute('class');
			elem = next ? elem.nextSibling : elem.previousSibling;

			//if (!elem) retour au début ou à la fin
			//	elem = next ? root.querySelector('li') : root.querySelector('li:last-child');

			if (elem)
				elem.setAttribute('class', 'foc');
		}
		else if (elem = root.querySelector('li')) {
			elem.setAttribute('class', 'foc');
		}
	};

	this.clear = function (ev) {

		var elem = ev.target.parentNode.querySelector('input');
		elem.value = '';
		shortcuts.filter({ target: elem });
		elem.focus();

		if (elem = document.getElementById('shortcutsHistory').querySelector('li.foc'))
			elem.removeAttribute('class');
	};

	this.unload = function () {
		this.storage('shortcutsHistory', this.storage('shortcutsHistory'));
	};

	this.storage = function (key, value) {

		// remove
		if (value === null) {
			localStorage.removeItem(key);
			sessionStorage.removeItem(key);
		}
		// set
		else if (typeof value != 'undefined') {
			localStorage.setItem(key, value);
			sessionStorage.setItem(key, value);
		}
		// get
		else {
			return localStorage.getItem(key) || sessionStorage.getItem(key);
		}
	};

})();

if (typeof self.addEventListener == 'function') {
	self.addEventListener('load', shortcuts.start.bind(shortcuts));
	self.addEventListener('beforeunload', shortcuts.unload.bind(shortcuts));
}