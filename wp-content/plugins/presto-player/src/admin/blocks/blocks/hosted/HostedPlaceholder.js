const { __ } = wp.i18n;
const { withNotices, BaseControl, ToggleControl, Notice } = wp.components;
const { dispatch } = wp.data;

import MediaPlaceholder from "@/admin/blocks/shared/placeholder";
import ProBadge from "@/admin/blocks/shared/components/ProBadge";

//constants
const ALLOWED_MEDIA_TYPES = ["video"];

export default withNotices(
  ({
    noticeUI,
    onSelect,
    children,
    onSelectURL,
    onUploadError,
    setAttributes,
    attributes,
  }) => {
    const { visibility } = attributes;
    const isPrivate = visibility === "private";

    return (
      <div>
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
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="presto-block-icon"
              >
                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
              </svg>
            )
          }
          labels={{
            title: isPrivate
              ? __("Presto Private Video", "presto-player")
              : __("Presto Video", "presto-player"),
            instructions: isPrivate
              ? __(
                  "Upload a video file, or pick one from your media library.",
                  "presto-player"
                )
              : __(
                  "Upload a video file, pick one from your media library, or add one with a URL.",
                  "presto-player"
                ),
          }}
          onSelect={onSelect}
          onSelectURL={onSelectURL}
          switcher={true}
          isPrivate={isPrivate}
          allowURLs={!isPrivate}
          accept="video/*"
          setAttributes={setAttributes}
          allowedTypes={ALLOWED_MEDIA_TYPES}
          value={attributes}
          notices={noticeUI}
          onError={onUploadError}
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
                        "This video is only accessible to those who are logged in.",
                        "presto-player"
                      )
                    : __(
                        "This video is currently accessible to everyone.",
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
                    "Private videos use php to stream your video. Keep in mind this will use up disk space and bandwidth so it may not be an appropriate choice for some hosts.",
                    "presto-player"
                  )}
                </div>
              </Notice>
            )}
          </div>
        </MediaPlaceholder>
      </div>
    );
  }
);
