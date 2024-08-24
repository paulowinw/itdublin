const { FormToggle } = wp.components;
export default () => {
  return (
    <>
      <span className="sfwd_option_label">
        <a className="sfwd_help_text_link" title="Click for Help!">
          <img
            alt=""
            src="//192.168.4.23:3004/wp-content/plugins/sfwd-lms/assets/images/question.png"
          />
          <label
            for="learndash-lesson-display-content-settings_lesson_use_presto_video"
            className="sfwd_label"
          >
            Use Presto Video
          </label>
        </a>
        <div
          id="learndash-lesson-display-content-settings_lesson_use_presto_video_tip"
          className="sfwd_help_text_div"
        >
          <label className="sfwd_help_text">
            Use the presto video in your post content for video progression.
          </label>
        </div>
      </span>
      <FormToggle checked={true} onChange={() => {}} />
    </>
  );
};
