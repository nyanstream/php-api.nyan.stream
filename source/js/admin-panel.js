'use strict'

var initAdminPanel = data => {
	/*
	 * @TODO Сделать скрытие форм при ошибке обработчика
	 */

	moment.tz.setDefault(data.timezone)
	Array.from($make.qs('form', ['a'])).forEach(form => { form.reset() })

	try {
		let
			needChangeTime = (data.sched.count > 0) ? 'true' : 'false',
			newTime = data.sched.latest.end

		if (needChangeTime) {
			$make.qs('.add-air input[type*=datetime]').setAttribute('value', moment.unix(newTime).format('YYYY-MM-DDTHH:mm'))
		}
	} catch (e) { }

	try {
		let infoEl = $make.qs('.rm-air .info')

		let emptySched = {
			anime: (infoEl && data.sched.count == 0) ? true : false
		}

		infoEl.innerHTML = (data.sched.count > 0 && infoEl)
			? `Название: <q>${data.sched.latest.title}</q>. Начало ${moment.unix(data.sched.latest.start).format('LLL')}; Конец: ${moment.unix(data.sched.latest.end).format('LLL')}`
			: 'Расписание пустое'

		let
			schedsNames = Object.keys(emptySched),
			emptyScheds = schedsNames.filter(key => emptySched[key] == true)

		if (schedsNames.length == emptyScheds.length) {
			Array.from($make.qs('.rm-air input', ['a'])).forEach(input => {
				input.setAttribute('disabled', '')
			})
		}
	} catch (e) { }

	try {
		let exprsSched = {
			anime: data.sched.countExpr
		}

		if (!$make.qs('.expired-clear')) { throw 42 }

		$make.qs('.expired-clear .count').innerHTML = `Всего просроченных эфиров: ${exprsSched['anime']}.`

		if (exprsSched['anime'] == 0) {
			Array.from($make.qs('.expired-clear input', ['a'])).forEach(input => {
				input.setAttribute('disabled', '')
			})
		}
	} catch (e) { }

	try {
		let vkLink = $make.qs('.vk-link p')
		if (!vkLink) { throw 42 }

		vkLink.appendChild($create.link(`${data.vk.URL}?client_id=${data.vk.appID}&display=page&redirect_uri=https://${data.server}/${data.vk.api}&scope=video,offline&response_type=code&state=vk-get-code`, 'Просто нажми сюда', '', ['e']))
	} catch (e) { }

	try {
		if (!$make.qs('.noti')) { throw 42 }

		let
			notiCreateF =        $make.qs('.noti input#noti_text'),
			notiCreateC =        $make.qs('.noti input#noti_color'),
			notiSubmitBtn =      $make.qs('.noti input[type="submit"]'),
			notiSubmitBtnText =  'Создать'

		if (data.noti.enabled == true) {
			let
				notiTextElemRoot = $make.qs('.noti .noti-text'),
				notiTextElem = $create.elem('samp', data.noti.text, '', ['s']),
				notiTextElemText = $create.text('Текущее оповещение: ')

			if (data.noti.color) {
				notiTextElem.style.backgroundColor = data.noti.color
			}

			notiTextElemRoot.appendChild(notiTextElemText)
			notiTextElemRoot.appendChild(notiTextElem)

			notiSubmitBtn.value = 'Заменить'
			notiSubmitBtnText = 'Заменить'
		}

		$make.qs('.noti input#noti_rm').addEventListener('change', e => {
			let _this = e.target

			if (_this.checked) {
				notiCreateF.setAttribute('disabled', '')
				notiCreateC.setAttribute('disabled', '')
				notiCreateF.value = ''
				notiSubmitBtn.value = 'Удалить'
			} else if (!_this.checked && notiCreateF.hasAttribute('disabled') && notiCreateC.hasAttribute('disabled')) {
				notiCreateF.removeAttribute('disabled')
				notiCreateC.removeAttribute('disabled', '')
				notiSubmitBtn.value = notiSubmitBtnText
			}
		})
	} catch (e) { }

	try {
		let
			tsAnime =      parseInt(data.ts.anime),
			tsRadio =      parseInt(data.ts.radio),
			tsNoti =       parseInt(data.ts.noti),
			currentTime =  parseInt(data.ts.current)

		let
			tsAnimeElem =    $make.qs('footer .tsAnime'),
			tsRadioElem =    $make.qs('footer .tsRadio'),
			tsNotiElem =     $make.qs('footer .tsNoti'),
			tsCurrentElem =  $make.qs('footer .tsCurrent')

		if (tsAnimeElem) { tsAnimeElem.textContent = moment.unix(tsAnime).from() }
		if (tsRadioElem) { tsRadioElem.textContent = moment.unix(tsRadio).from() }
		if (tsNotiElem) { tsNotiElem.textContent = moment.unix(tsNoti).from() }

		if (tsCurrentElem) {
			setInterval(() => {
				tsCurrentElem.textContent = moment.unix(currentTime).format('LL LTS')
				++currentTime
			}, 1000)
		}
	} catch (e) {}
}
