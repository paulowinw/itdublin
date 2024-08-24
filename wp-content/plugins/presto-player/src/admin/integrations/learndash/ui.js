export default () => {
  const videoUrlField = jQuery(
      `#learndash-lesson-display-content-settings_lesson_video_url,
      #learndash-topic-display-content-settings_lesson_video_url`
    ),
    hideSettings = jQuery(
      `#learndash-lesson-display-content-settings_lesson_video_url_field,
      #learndash-lesson-display-content-settings_lesson_video_focus_pause_field,
      #learndash-lesson-display-content-settings_lesson_video_auto_start_field,
      #learndash-lesson-display-content-settings_lesson_video_show_controls_field,
      #learndash-lesson-display-content-settings_lesson_video_track_time_field,

      #learndash-topic-display-content-settings_lesson_video_url_field,
      #learndash-topic-display-content-settings_lesson_video_focus_pause_field,
      #learndash-topic-display-content-settings_lesson_video_auto_start_field,
      #learndash-topic-display-content-settings_lesson_video_show_controls_field,
      #learndash-topic-display-content-settings_lesson_video_track_time_field`
    ),
    prestoToggle = jQuery(
      `#learndash-lesson-display-content-settings_lesson_use_presto_video,
      #learndash-topic-display-content-settings_lesson_use_presto_video`
    );

  const checkURL = () => {
    let video_url = videoUrlField.val();

    if (video_url.includes("(presto")) {
      hideSettings.hide();
      prestoToggle.prop("checked", true);
    }
  };

  checkURL();

  prestoToggle.on("change", () => {
    if (prestoToggle.prop("checked")) {
      videoUrlField.val("(presto)");
      hideSettings.hide();
    } else {
      hideSettings.show();
      if (videoUrlField.val().includes("(presto")) {
        videoUrlField.val("");
      }
    }
  });
};
