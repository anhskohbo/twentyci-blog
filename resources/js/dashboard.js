import Editor from '@toast-ui/editor';
import flatpickr from 'flatpickr';

import './bootstrap';
import { domReady } from './utils/dom';
import { SwalConfirm } from './utils/sweetalert';

import 'codemirror/lib/codemirror.css';
import '@toast-ui/editor/dist/toastui-editor.css';
import 'flatpickr/dist/flatpickr.min.css';

function initPostEdit() {
  let editorElement = document.getElementById('editor');

  if (editorElement) {
    let editor;
    let contentElement = editorElement.closest('form').querySelector('textarea[name="content"]');

    editor = new Editor({
      el: editorElement,
      initialValue: contentElement ? contentElement.value : '',
      initialEditType: 'markdown',
      hideModeSwitch: true,
      height: '550px',
      previewStyle: 'tab',
      usageStatistics: false,
      events: {
        change() {
          if (contentElement) {
            contentElement.value = editor.getMarkdown();
          }
        },
      },
    });
  }
}

function initDeleteAction() {
  $(document).on('click', '.delete-action', async (e) => {
    e.preventDefault();

    let action = $(e.currentTarget).data('action');
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    let { isConfirmed } = await SwalConfirm();

    if (isConfirmed) {
      const template = `
        <form action="${action}" method="POST" style="display: none">
           <input type="hidden" name="_method" value="DELETE" />
           <input type="hidden" name="_token" value="${csrfToken}" />
        </form>
      `;

      const form = $(template).appendTo('body');
      form.submit();
    }
  });
}

domReady(() => {
  const container = document.getElementById('page-wrapper');
  if (!container) {
    return;
  }

  let currentPage = container.getAttribute('data-page');

  switch (currentPage) {
    case 'edit-post':
      initPostEdit();
      break;
  }

  initDeleteAction();

  flatpickr('input[data-init="flatpickr"]', {
    enableTime: false,
    allowInput: false,
    dateFormat: 'Y-m-d',
    disableMobile: true,
  });

  flatpickr('input[data-init="flatpickr-datetime"]', {
    dateFormat: 'Y-m-d H:i',
    enableTime: true,
    allowInput: false,
    disableMobile: true,
  });
});
