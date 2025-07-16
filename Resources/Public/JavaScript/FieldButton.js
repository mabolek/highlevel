import RegularEvent from '@typo3/core/event/regular-event.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Notification from '@typo3/backend/notification.js';

new RegularEvent('click', function (e) {
	const self = this;

	const filtered = Object.values(window.TYPO3.FormEngine.formElement)
		.reduce((obj, field) => {
			if (typeof field.name !== 'string' || field.name === '' || !field.name.match(/^data\[/)) {
				return obj;
			}

			obj[field.name] = field.value;

			return obj;
		}, {});

	const params = {
		fields: filtered,
		value: filtered['data' + self.getAttribute('data-formengine-input-name')],
		identifier: self.getAttribute('data-highlevel-identifier'),
		inputName: self.getAttribute('data-formengine-input-name'),
		outputName: self.getAttribute('data-formengine-output-name')
	};
console.log(params);
	new AjaxRequest(TYPO3.settings.ajaxUrls.highlevel_fieldbutton)
		.post(params, {
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			}
		})
		.then(async function (result) {
			if (!result.response.ok) {
				Notification.error(
					'Action Failed',
					'The action could not be completed because the server returned an error. ' + result.response.statusText + ' (' + result.response.status + ')'
				);

				return;
			}

			const data = await result.resolve();

			if (!data.success) {
				Notification.error(
					'Action Error',
					data.error
				);

				return;
			}

			document.querySelector('input[name="data' + self.getAttribute('data-formengine-output-name') + '"]').value = data.value;
			document.querySelector('input[data-formengine-input-name="data' + self.getAttribute('data-formengine-output-name') + '"]').value = data.value;
		}, function (error) {
			Notification.error(
				'Request Failed',
				'The request failed. ' + error.response.statusText + ' (' + error.response.status + ')'
			);
		});
}).bindTo(document.querySelector('.highLevelFieldButton'));
