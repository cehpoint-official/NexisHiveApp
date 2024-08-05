$(document).ready(function () {
  $(".sideBarli").removeClass("activeLi");
  $(".indexSideA").addClass("activeLi");
 

  $("#settingsForm").on("submit", function (event) {
    event.preventDefault();
    var formdata = new FormData($("#settingsForm")[0]);
    console.log(formdata);

    $.ajax({
      url: `${domainUrl}saveSettings`,
      type: "POST",
      data: formdata,
      dataType: "json",
      contentType: false,
      cache: false,
      processData: false,
      success: function (response) {
        console.log(response);
        $(".loader").hide();

        if (response.status == false) {
          iziToast.error({
            title: "Error!",
            message: response.message,
            position: "topRight",
          });
        } else {
          iziToast.success({
            title: "Success!",
            message: response.message,
            position: "topRight",
          });
        }
      },
      error: function (err) {
        console.log(err);
      },
    });
  });

});
