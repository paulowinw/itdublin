const { __ } = wp.i18n;
const { TextareaControl } = wp.components;
const { useState } = wp.element;

export default ({ text, update, onFocus }) => {
  const [more, setMore] = useState(false);

  return (
    <div style={{ display: "block", width: "100%" }}>
      <TextareaControl
        label="Text"
        help={
          <span>
            {__("This field accepts", "presto-player")}{" "}
            <a
              href="#"
              onClick={(e) => {
                setMore(!more);
                e.preventDefault();
              }}
            >
              {__("Dynamic Data", "presto-player")}
            </a>
            {!!more && (
              <div style={{ marginTop: "20px" }}>
                {__(
                  "This field will also accept dynamic values that we will replace with dynamic content: {user.user_login}, {user.user_nicename}, {user.user_email}, {user.user_url},{user.user_registered}, {user.display_name}, {site.url}, {site.name}, {ip_address}",
                  "presto-player"
                )}
              </div>
            )}
          </span>
        }
        className={"presto-player__overlay--text"}
        placeholder={__("Enter some text.", "presto-player")}
        value={text || ""}
        onChange={(text) => update({ text })}
        autoComplete="off"
        onFocus={onFocus}
      />
    </div>
  );
};
