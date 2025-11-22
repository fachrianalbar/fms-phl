function showSwal(options = {}) {
    const {
        title = "Are you sure?",
        text = "",
        icon = "warning",
        buttons = true,
        dangerMode = false,
        thenConfirmed = null,
        thenCancelled = null,
    } = options;

    swal({
        title: title,
        text: text,
        icon: icon,
        buttons: buttons,
        dangerMode: dangerMode,
    }).then((willDo) => {
        if (willDo && typeof thenConfirmed === "function") {
            thenConfirmed();
        } else if (!willDo && typeof thenCancelled === "function") {
            thenCancelled();
        }
    });
}
