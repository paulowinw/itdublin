import MediaPlaceholder from "@/admin/blocks/shared/audio-placeholder";
const { __ } = wp.i18n;
const { withNotices, BaseControl, ToggleControl, Notice } = wp.components;
import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import { dispatch } from "@wordpress/data";

const ALLOWED_MEDIA_TYPES = ["audio"];

export default ({
  attributes,
  setAttributes,
  onSelectURL,
  onSelect,
  children,
}) => {
  const { visibility } = attributes;
  const isPrivate = visibility === "private";

  return (
    <MediaPlaceholder
      icon={
        isPrivate ? (
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            className="presto-block-icon"
          >
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
        ) : (
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            className="presto-block-icon"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
            />
          </svg>
        )
      }
      labels={{
        title: __("Presto Audio", "presto-player"),
        instructions: __(
          "Upload a audio file, or pick one from your media library.",
          "presto-player"
        ),
      }}
      accept="audio/*"
      setAttributes={setAttributes}
      allowedTypes={ALLOWED_MEDIA_TYPES}
      switcher={true}
      isPrivate={isPrivate}
      allowURLs={!isPrivate}
      onSelectURL={onSelectURL}
      onSelect={onSelect}
      value={attributes}
      allowURLs={true}
      onError={() => console.log("error")}
    >
      <div style={{ width: "100%" }}>
        <BaseControl className="presto-player__placeholder-control">
          <ToggleControl
            label={
              <div>
                {__("Make Private", "presto-player")}{" "}
                {!prestoPlayer?.isPremium && <ProBadge />}
              </div>
            }
            help={
              isPrivate
                ? __(
                    "This audio is only accessible to those who are logged in.",
                    "presto-player"
                  )
                : __(
                    "This audio is currently accessible to everyone.",
                    "presto-player"
                  )
            }
            checked={isPrivate}
            onChange={(isPrivate) => {
              if (!prestoPlayer?.isPremium) {
                dispatch("presto-player/player").setProModal(true);
                return;
              }
              setAttributes({
                visibility: isPrivate ? "private" : "public",
              });
            }}
          />
        </BaseControl>

        {!!children && <div style={{ width: "100%" }}>{children}</div>}

        {!!isPrivate && (
          <Notice status="warning" isDismissible={false}>
            <div>
              <div>
                <strong>{__("Note", "presto-player")}</strong>
              </div>
              {__(
                "Private audio uses php to stream your audio. Keep in mind this will use up disk space and bandwidth so it may not be an appropriate choice for some hosts.",
                "presto-player"
              )}
            </div>
          </Notice>
        )}
      </div>
    </MediaPlaceholder>
  );
};
