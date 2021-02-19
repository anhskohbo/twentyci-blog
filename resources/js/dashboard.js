import Editor from '@toast-ui/editor';

import './bootstrap';
import { domReady } from './utils/dom';

import 'codemirror/lib/codemirror.css';
import '@toast-ui/editor/dist/toastui-editor.css';

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
});
