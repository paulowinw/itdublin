import { PrestoPlaylistItem } from "@presto-player/components-react";
import {
  InspectorControls,
  RichText,
  useBlockProps,
} from "@wordpress/block-editor";
import { PanelBody, TextControl } from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";

export default (props) => {
  const { attributes, setAttributes, context } = props;
  const { id, title, duration } = attributes;
  const blockProps = useBlockProps();

  const { entityTitle } = useSelect(
    (select) => {
      const queryArgs = ["postType", "pp_video_block", id];
      const videoRecord = select(coreStore).getEntityRecord(...queryArgs);
      return {
        entityTitle: videoRecord ? videoRecord?.title : null,
      };
    },
    [id]
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("General", "presto-player")} initialOpen={true}>
          <TextControl
            label={__("Title", "presto-player")}
            value={title}
            onChange={(value) => {
              setAttributes({ title: value });
            }}
          />
          <TextControl
            label={__("Duration", "presto-player")}
            value={duration}
            onChange={(value) => {
              setAttributes({ duration: value });
            }}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <PrestoPlaylistItem
          active={context["presto-player/playlist-media-id"] === id}
        >
          <div className="item-title" slot="item-title">
            <RichText
              tagName="span"
              value={!title ? entityTitle : title}
              allowedFormats={[]}
              onChange={(title) => setAttributes({ title })}
              placeholder={__("Title...", "presto-player")}
            />
          </div>
          <div className="item-duration" slot="item-duration">
            <RichText
              tagName="span"
              value={duration}
              allowedFormats={[]}
              onChange={(duration) => setAttributes({ duration })}
            />
          </div>
        </PrestoPlaylistItem>
      </div>
    </>
  );
};
