window.SequelSuccessToast = Swal.mixin({
    toast            : true,
    position         : "bottom",
    title            : "Success",
    type             : "success",
    showConfirmButton: false,
    timer: 3000,
});

window.SequelErrorToast = Swal.mixin({
    toast            : true,
    position         : "bottom",
    title            : "Oops...",
    type             : "error",
    showConfirmButton: false,
    timer            : 3000,
    customClass      : "",
});
