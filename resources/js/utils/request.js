import $ from 'jquery';

export async function sendRequest(url, data, method = 'POST') {
  try {
    return await $.ajax({
      url,
      data,
      type: method,
      dataType: 'json',
      processData: !(data instanceof FormData),
      contentType: (data instanceof FormData)
        ? false
        : 'application/x-www-form-urlencoded; charset=UTF-8',
    });
  } catch (e) {
    if (e.responseJSON && e.responseJSON.message) {
      console.warn(e.responseJSON.message, 'warning');
    }

    throw e;
  }
}
