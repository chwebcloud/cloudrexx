cx.jQuery(document).ready(function() {

    var selectLanguageFileForm = cx.jQuery('#form-language-file-select');
    // reload page automatically when changing language
    selectLanguageFileForm.change(function() {
        cx.jQuery(this).submit();
    });

    // set width of form labels according to the longest content equally
    var equalWidth = 0;
    var formLabels = cx.jQuery("#form-0 .group label");
    formLabels.each(function() {
        if (cx.jQuery(this).width() > equalWidth) {
            equalWidth = cx.jQuery(this).width();
        }
    });
    formLabels.width(equalWidth);

    // wrap names of placeholder inputs properly
    // to get all values in subarray of post when submitting the form
    var placeholderInputs = cx.jQuery("#form-0 input[type='text']");
    placeholderInputs.each(function() {
        var placeholderName = cx.jQuery(this).attr('name');
        var wrappedName = "placeholders[" + placeholderName + "]";
        cx.jQuery(this).attr('name', wrappedName);
    });

    // add reset button to each placeholder
    cx.jQuery("#form-0 .group").append(
      "<input type=\"button\" class=\"reset-placeholder\" value=\"" +
      cx.variables.get("resetText", "Locale/LanguageFile") +
      "\" />"
    );

    cx.jQuery("input.reset-placeholder").click(function() {
        resetPlaceholder(this);
    });

    function resetPlaceholder(button) {
        var placeholderName = cx.jQuery(button).siblings("label").html();
        var languageCode = cx.jQuery("select[name='languageCode'").val();
        var componentName = cx.jQuery("select[name='componentName'").val();
        var frontend = cx.jQuery("#subnavbar_level2 ul li a[title='Frontend']").hasClass("active");

        cx.ajax(
          "Locale",
          "getPlaceholderDefaultValue",
          {
              data: {
                  placeholderName: placeholderName,
                  languageCode: languageCode,
                  frontend: frontend,
                  componentName: componentName
              },
              success: function(json) {
                  if (json.data) {
                      cx.jQuery(button).siblings(".controls").children("input").val(json.data);
                      cx.ui.messages.add(
                        cx.variables.get("resetSuccess", "Locale/LanguageFile"),
                        "success"
                      );
                  } else {
                      cx.ui.messages.add(
                        cx.variables.get("resetError", "Locale/LanguageFile"),
                        "error"
                      );
                  }
              }
          }
        );

    }
});