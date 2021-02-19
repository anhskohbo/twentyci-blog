import Swal from 'sweetalert2';

export const Toast = (options = {}) => {
  return Swal.fire({
    ...options,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    animation: true,
    timer: 15000,
  });
};

export const SwalConfirm = (message, options = {}) => {
  return Swal.fire({
    ...options,
    text: message || 'Are you sure you want to perform this action?',
    position: 'center',
    backdrop: 'rgba(0,0,0,.8)',
    reverseButtons: true,
    buttonsStyling: false,
    showCancelButton: true,
    customClass: {
      popup: 'Swal2ConfirmDialog',
      cancelButton: '',
      confirmButton: '',
    },
  });
};

export const SwalAlert = (message, options) => {
  return Swal.fire({
    text: message,
    position: 'center',
    backdrop: 'rgba(0,0,0,.8)',
    reverseButtons: true,
    buttonsStyling: false,
    showCancelButton: false,
    customClass: {
      popup: 'Swal2ConfirmDialog Swal2ConfirmDialog--alert',
      cancelButton: '',
      confirmButton: '',
    },
    ...options,
  });
};
