import selector from "./selector";

jQuery(window).on("elementor/frontend/init", () => {
  if (typeof elementor === "undefined") {
    return;
  }

  elementor.channels.editor.on("presto:video:edit", function (view) {
    var block_id = view.elementSettingsModel.get("video_block");
    if (!block_id) {
      return;
    }

    var win = window.open(
      prestoEditorData.siteURL +
        `/wp-admin/post.php?post=${block_id}&action=edit`,
      "_blank"
    );
    win.focus();
  });

  // dynamic links
  elementor.channels.editor.on(
    "editor:widget:presto_video:section_video:activated",
    function (view) {
      selector(view.$el.find(".elementor-select2"));
      view.model.get("settings").on("change", (model) => {});
    }
  );
  elementor.channels.editor.on("presto:video:create", function () {
    var win = window.open(
      prestoEditorData.siteURL +
        `/wp-admin/post-new.php?post_type=pp_video_block`,
      "_blank"
    );
    win.focus();
  });
});
