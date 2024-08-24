import HostedAudioPlaceholder from "./HostedAudioPlaceholder";
import { InspectorControls, BlockControls } from "@wordpress/block-editor";
import AudioBlockInspectorControl from "./AudioBlockInspectorControl";
import Player from "@/admin/blocks/shared/Player";
import { compose } from "@wordpress/compose";
import withPlayerEdit from "./with-player-edit";
import withPlayerData from "./with-player-data";
import {
  Placeholder,
  Spinner,
  Disabled,
  withNotices,
  Toolbar,
  Button,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import Editing from "../../shared/Editing";

export default compose([withPlayerData(), withPlayerEdit()])(
  withNotices(
    ({
      attributes,
      setAttributes,
      branding,
      presetData,
      renderKey,
      defaultPreset,
      lockSave,
      unlockSave,
      loading,
      createVideo,
      onRemoveSrc,
    }) => {
      const { poster, src, id } = attributes;

      const onSelectURL = (newUrl) => {
        setAttributes({
          ...attributes,
          src: newUrl,
          title: newUrl,
          preset: defaultPreset?.id,
        });
        lockSave();
        createVideo({
          src: newUrl,
          type: "link",
        })
          .catch((e) => {
            setAttributes({ src: "" });
            showNotice(e);
          })
          .finally(unlockSave);
      };
      // const tracks = ''
      function onSelectAudio(audio) {
        if (!audio || !audio.url) {
          // in this case there was an error
          // previous attributes should be removed
          // because they may be temporary blob urls
          setAttributes({ src: undefined, id: undefined });
          return;
        }
        // sets the block's attribute and updates the edit component from the
        // selected media
        if (audio.title) {
          setAttributes({
            src: audio.url,
            preset: defaultPreset?.id,
            title: audio.title,
            attachment_id: audio.id,
          });

          lockSave();
          createVideo({
            src: audio.url,
            type: "attachment",
            attachment_id: audio.id,
          })
            .catch((e) => {
              setAttributes({ src: "" });
              showNotice(e);
            })
            .finally(unlockSave);
        } else {
          setAttributes({
            src: audio.url,
            title: audio.url,
            preset: defaultPreset?.id,
            // attachment_id: audio.id,
          });
        }
      }

      if (!src) {
        return (
          <>
            <HostedAudioPlaceholder
              attributes={attributes}
              setAttributes={setAttributes}
              onSelectURL={onSelectURL}
              onSelect={onSelectAudio}
            ></HostedAudioPlaceholder>
          </>
        );
      }

      // loading presets still
      if (loading || !id) {
        return (
          <Placeholder className="presto-player__placeholder is-loading">
            <Spinner />
          </Placeholder>
        );
      }

      return (
        <>
          <BlockControls>
            {/* <AudioTranscription
              attributes={attributes}
              tracks={tracks}
              onChange={(newTracks) => {
                setAttributes({ tracks: newTracks });
              }}
            /> */}
            <Toolbar>
              <Button onClick={() => onRemoveSrc()}>
                {__("Replace", "presto-player")}
              </Button>
            </Toolbar>
            <Editing />
          </BlockControls>

          {/* Enable InspectorControls */}
          <InspectorControls>
            <AudioBlockInspectorControl
              attributes={attributes}
              setAttributes={setAttributes}
            />
          </InspectorControls>

          <figure>
            {/*
              Disable the audio tag so the user clicking on it won't play the
              audio when the controls are enabled.
              */}
            <Disabled>
              <Player
                poster={poster}
                src={src}
                id={id}
                type={"audio"}
                attributes={attributes}
                setAttributes={setAttributes}
                preset={presetData}
                branding={branding}
                key={renderKey}
              />
            </Disabled>
          </figure>
        </>
      );
    }
  )
);
