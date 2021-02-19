export function domReady(callback) {
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    return void callback();
  }

  // DOMContentLoaded has not fired yet, delay callback until then.
  document.addEventListener('DOMContentLoaded', callback);
}

export const getSelector = element => {
  let selector = element.getAttribute('data-target');

  if (!selector || selector === '#') {
    const hrefAttr = element.getAttribute('href');

    selector = hrefAttr && hrefAttr !== '#' ? hrefAttr.trim() : null;
  }

  return selector;
};

export const getSelectorFromElement = element => {
  const selector = getSelector(element);

  if (selector) {
    return document.querySelector(selector) ? selector : null;
  }

  return null;
};

export const getElementFromSelector = element => {
  const selector = getSelector(element);

  return selector ? document.querySelector(selector) : null;
};

export const emulateTransitionEnd = (element, duration) => {
  let called = false;
  const durationPadding = 5;
  const emulatedDuration = duration + durationPadding;

  function listener() {
    called = true;
    element.removeEventListener('transitionend', listener);
  }

  element.addEventListener('transitionend', listener);

  setTimeout(() => {
    if (!called) {
      element.dispatchEvent(new Event('transitionend'));
    }
  }, emulatedDuration);
};

export const getTransitionDurationFromElement = element => {
  if (!element) {
    return 0;
  }

  // Get transition-duration of the element
  let { transitionDuration, transitionDelay } = window.getComputedStyle(element);

  const floatTransitionDuration = Number.parseFloat(transitionDuration);
  const floatTransitionDelay = Number.parseFloat(transitionDelay);

  // Return 0 if element or transition duration is not found
  if (!floatTransitionDuration && !floatTransitionDelay) {
    return 0;
  }

  // If multiple durations are defined, take the first
  transitionDuration = transitionDuration.split(',')[0];
  transitionDelay = transitionDelay.split(',')[0];

  return (Number.parseFloat(transitionDuration) + Number.parseFloat(transitionDelay)) * 1000;
};
