/**
 * WordPress dependencies
 */
// import { __, sprintf } from '@wordpress/i18n';
const { __, sprintf } = wp.i18n;
const {
  NavigableMenu,
  MenuItem,
  FormFileUpload,
  MenuGroup,
  ToolbarGroup,
  ToolbarButton,
  Dropdown,
  SVG,
  Rect,
  Path,
  Button,
  TextControl,
  SelectControl,
} = wp.components;
const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { useSelect } = wp.data;
const { useState } = wp.element;

const ALLOWED_TYPES = ["text/vtt"];

const DEFAULT_KIND = "subtitles";

// const KIND_OPTIONS = [
//   { label: __("Subtitles"), value: "subtitles" },
//   { label: __("Captions"), value: "captions" },
//   { label: __("Descriptions"), value: "descriptions" },
//   { label: __("Chapters"), value: "chapters" },
//   { label: __("Metadata"), value: "metadata" },
// ];

const captionIcon = (
  <svg viewBox="0 0 29 25" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M17.5014 20.2854H28.6316V0.764648H0.110825V20.2854H11.241L14.3712 24.2854L17.5014 20.2854ZM14.3712 21.0401L16.5269 18.2854H26.6316V2.76465H2.11082V18.2854H12.2155L14.3712 21.0401Z"
    />
    <path d="M10.4503 14.9446C9.56226 14.9446 8.76226 14.7606 8.05026 14.3926C7.33826 14.0166 6.77826 13.4966 6.37026 12.8326C5.97026 12.1606 5.77026 11.4006 5.77026 10.5526C5.77026 9.70464 5.97026 8.94864 6.37026 8.28464C6.77826 7.61264 7.33826 7.09264 8.05026 6.72464C8.76226 6.34864 9.56226 6.16064 10.4503 6.16064C11.2663 6.16064 11.9943 6.30464 12.6343 6.59264C13.2743 6.88064 13.8023 7.29664 14.2183 7.84064L12.4303 9.43664C11.9103 8.78064 11.2983 8.45264 10.5943 8.45264C10.0023 8.45264 9.52626 8.64464 9.16626 9.02864C8.80626 9.40464 8.62626 9.91264 8.62626 10.5526C8.62626 11.1926 8.80626 11.7046 9.16626 12.0886C9.52626 12.4646 10.0023 12.6526 10.5943 12.6526C11.2983 12.6526 11.9103 12.3246 12.4303 11.6686L14.2183 13.2646C13.8023 13.8086 13.2743 14.2246 12.6343 14.5126C11.9943 14.8006 11.2663 14.9446 10.4503 14.9446Z" />
    <path d="M19.2042 14.9446C18.3162 14.9446 17.5162 14.7606 16.8042 14.3926C16.0922 14.0166 15.5322 13.4966 15.1242 12.8326C14.7242 12.1606 14.5242 11.4006 14.5242 10.5526C14.5242 9.70464 14.7242 8.94864 15.1242 8.28464C15.5322 7.61264 16.0922 7.09264 16.8042 6.72464C17.5162 6.34864 18.3162 6.16064 19.2042 6.16064C20.0202 6.16064 20.7482 6.30464 21.3882 6.59264C22.0282 6.88064 22.5562 7.29664 22.9722 7.84064L21.1842 9.43664C20.6642 8.78064 20.0522 8.45264 19.3482 8.45264C18.7562 8.45264 18.2802 8.64464 17.9202 9.02864C17.5602 9.40464 17.3802 9.91264 17.3802 10.5526C17.3802 11.1926 17.5602 11.7046 17.9202 12.0886C18.2802 12.4646 18.7562 12.6526 19.3482 12.6526C20.0522 12.6526 20.6642 12.3246 21.1842 11.6686L22.9722 13.2646C22.5562 13.8086 22.0282 14.2246 21.3882 14.5126C20.7482 14.8006 20.0202 14.9446 19.2042 14.9446Z" />
  </svg>
);

function TrackList({ tracks, onEditPress }) {
  let content;
  if (tracks.length === 0) {
    content = (
      <p className="block-library-video-tracks-editor__tracks-informative-message">
        {__(
          "Captions are .vtt files that help make your content more accesible to a wider range of users.",
          "presto-player"
        )}
      </p>
    );
  } else {
    content = tracks.map((track, index) => {
      return (
        <div
          key={index}
          className="block-library-video-tracks-editor__track-list-track"
        >
          <span>{track.label} </span>
          <Button
            isTertiary
            onClick={() => onEditPress(index)}
            aria-label={sprintf(
              /* translators: %s: Label of the video text track e.g: "French subtitles" */
              __("Edit %s", "presto-player"),
              track.label
            )}
          >
            {__("Edit", "presto-player")}
          </Button>
        </div>
      );
    });
  }
  return (
    <MenuGroup
      label={__("Captions", "presto-player")}
      className="block-library-video-tracks-editor__track-list"
    >
      {content}
    </MenuGroup>
  );
}

function SingleTrackEditor({ track, onChange, onClose, onRemove }) {
  const { src = "", label = "", srcLang = "", kind = DEFAULT_KIND } = track;
  const fileName = src.startsWith("blob:")
    ? ""
    : src.substring(src.lastIndexOf("/") + 1);
  return (
    <NavigableMenu>
      <div className="block-library-video-tracks-editor__single-track-editor">
        <span className="block-library-video-tracks-editor__single-track-editor-edit-track-label">
          {__("Edit caption track", "presto-player")}
        </span>
        <span>
          {__("File", "presto-player")}: <b>{fileName}</b>
        </span>
        <div className="block-library-video-tracks-editor__single-track-editor-label-language">
          <TextControl
            /* eslint-disable jsx-a11y/no-autofocus */
            autoFocus
            /* eslint-enable jsx-a11y/no-autofocus */
            onChange={(newLabel) =>
              onChange({
                ...track,
                label: newLabel,
              })
            }
            label={__("Label", "presto-player")}
            value={label}
            help={__("Title of track", "presto-player")}
          />
          <TextControl
            onChange={(newSrcLang) =>
              onChange({
                ...track,
                srcLang: newSrcLang,
              })
            }
            label={__("Source language", "presto-player")}
            value={srcLang}
            help={__("Language tag (en, fr, etc.)", "presto-player")}
          />
        </div>
        {/* <SelectControl
          className="block-library-video-tracks-editor__single-track-editor-kind-select"
          options={KIND_OPTIONS}
          value={kind}
          label={__("Kind")}
          onChange={(newKind) => {
            if (newKind === DEFAULT_KIND) {
              newKind = undefined;
            }
            onChange({
              ...track,
              kind: newKind,
            });
          }}
        /> */}
        <div className="block-library-video-tracks-editor__single-track-editor-buttons-container">
          <Button
            isSecondary
            onClick={() => {
              const changes = {};
              let hasChanges = false;
              if (label === "") {
                changes.label = __("English", "presto-player");
                hasChanges = true;
              }
              if (srcLang === "") {
                changes.srcLang = "en";
                hasChanges = true;
              }
              if (hasChanges) {
                onChange({
                  ...track,
                  ...changes,
                });
              }
              onClose();
            }}
          >
            {__("Close", "presto-player")}
          </Button>
          <Button isDestructive isLink onClick={onRemove}>
            {__("Remove track", "presto-player")}
          </Button>
        </div>
      </div>
    </NavigableMenu>
  );
}

export default function TracksEditor({ tracks = [], onChange }) {
  const mediaUpload = useSelect((select) => {
    return select("core/block-editor").getSettings().mediaUpload;
  }, []);
  const [trackBeingEdited, setTrackBeingEdited] = useState(null);

  if (!mediaUpload) {
    return null;
  }
  return (
    <Dropdown
      contentClassName="block-library-video-tracks-editor"
      renderToggle={({ isOpen, onToggle }) => (
        <ToolbarGroup>
          <ToolbarButton
            label={__("Captions", "presto-player")}
            showTooltip
            aria-expanded={isOpen}
            aria-haspopup="true"
            onClick={onToggle}
            icon={captionIcon}
          />
        </ToolbarGroup>
      )}
      renderContent={({}) => {
        if (trackBeingEdited !== null) {
          return (
            <SingleTrackEditor
              track={tracks[trackBeingEdited]}
              onChange={(newTrack) => {
                const newTracks = [...tracks];
                newTracks[trackBeingEdited] = newTrack;
                onChange(newTracks);
              }}
              onClose={() => setTrackBeingEdited(null)}
              onRemove={() => {
                onChange(
                  tracks.filter((_track, index) => index !== trackBeingEdited)
                );
                setTrackBeingEdited(null);
              }}
            />
          );
        }
        return (
          <>
            <NavigableMenu>
              <TrackList tracks={tracks} onEditPress={setTrackBeingEdited} />
              <MenuGroup
                className="block-library-video-tracks-editor__add-tracks-container"
                label={__("Add caption languages", "presto-player")}
              >
                <MediaUpload
                  onSelect={({ url }) => {
                    const trackIndex = tracks.length;
                    onChange([...tracks, { src: url }]);
                    setTrackBeingEdited(trackIndex);
                  }}
                  allowedTypes={ALLOWED_TYPES}
                  render={({ open }) => (
                    <MenuItem icon={"media"} onClick={open}>
                      {__("Open Media Library", "presto-player")}
                    </MenuItem>
                  )}
                />
                <MediaUploadCheck>
                  <FormFileUpload
                    onChange={(event) => {
                      const files = event.target.files;
                      const trackIndex = tracks.length;
                      mediaUpload({
                        allowedTypes: ALLOWED_TYPES,
                        filesList: files,
                        onFileChange: ([{ url }]) => {
                          const newTracks = [...tracks];
                          if (!newTracks[trackIndex]) {
                            newTracks[trackIndex] = {};
                          }
                          newTracks[trackIndex] = {
                            ...tracks[trackIndex],
                            src: url,
                          };
                          onChange(newTracks);
                          setTrackBeingEdited(trackIndex);
                        },
                      });
                    }}
                    accept=".vtt,text/vtt"
                    render={({ openFileDialog }) => {
                      return (
                        <MenuItem
                          icon={"upload"}
                          onClick={() => {
                            openFileDialog();
                          }}
                        >
                          {__("Upload", "presto-player")}
                        </MenuItem>
                      );
                    }}
                  />
                </MediaUploadCheck>
              </MenuGroup>
            </NavigableMenu>
          </>
        );
      }}
    />
  );
}
