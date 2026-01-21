import RegularEvent from '@typo3/core/event/regular-event.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Notification from '@typo3/backend/notification.js';

for (const fieldButtonElement of document.querySelectorAll('.highlevelFieldButton').values()) {
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

    let sourceName = self.getAttribute('data-highlevel-source-name');

    let value = filtered['data' + sourceName];

    if (sourceName.endsWith('[uid]')) {
      value = sourceName.split('][')[1];
    }

    const params = {
      value: value,
      identifier: self.getAttribute('data-highlevel-identifier'),
      sourceName: sourceName,
      targetName: self.getAttribute('data-highlevel-target-name')
    };
    console.log(params);
    new AjaxRequest(TYPO3.settings.ajaxUrls['highlevel_' + params.identifier])
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

        const response = await result.resolve();

        if (!response.success) {
          Notification.error(
            'Action Error',
            response.error
          );

          return;
        }

        document.querySelector('input[name="data' + self.getAttribute('data-highlevel-target-name') + '"]').value = response.data.value;
        document.querySelector('input[data-formengine-input-name="data' + self.getAttribute('data-highlevel-target-name') + '"]').value = response.data.value;
      }, function (error) {
        Notification.error(
          'Request Failed',
          'The request failed. ' + error.response.statusText + ' (' + error.response.status + ')'
        );
      });
  }).bindTo(fieldButtonElement);
}
