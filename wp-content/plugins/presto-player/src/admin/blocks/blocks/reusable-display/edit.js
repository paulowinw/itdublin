/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import {
  useBlockProps,
  useInnerBlocksProps,
  store as blockEditorStore,
  __experimentalUseBlockPreview as useBlockPreview,
} from "@wordpress/block-editor";
import { BlockControls, InspectorControls } from "@wordpress/block-editor";
import {
  Button,
  Placeholder,
  Spinner,
  Toolbar,
  PanelBody,
  BaseControl,
  Flex,
} from "@wordpress/components";
import { store as coreStore, useEntityBlockEditor } from "@wordpress/core-data";
import { useSelect, useDispatch, select } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import ProvidersPlaceholder from "../../shared/ProvidersPlaceholder/ProvidersPlaceholder";
import { Icon, symbolFilled, edit } from "@wordpress/icons";
import { useState } from "@wordpress/element";
import EditContext from "./context";
import { useEffect } from "@wordpress/element";

export default ({ attributes, context, clientId, isSelected }) => {
  const [isEditing, setIsEditing] = useState(false);
  const { selectBlock } = useDispatch(blockEditorStore);
  const { id: idAttribute } = attributes;
  const id = context["presto-player/playlist-media-id"] || idAttribute;
  const blockProps = useBlockProps();
  const [blocks, onInput, onChange] = useEntityBlockEditor(
    "postType",
    "pp_video_block",
    { id }
  );

  const mediaBlocks = (blocks || []).filter(
    (block) => block.name === "presto-player/reusable-edit"
  );

  const hasSrc = (mediaBlocks?.[0]?.innerBlocks || []).some(
    (block) => block.attributes.src
  );

  const blockPreviewProps = useBlockPreview({
    blocks: mediaBlocks,
  });

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    value: blocks,
    onInput,
    onChange,
    templateLock: "all",
  });

  const { media, canEdit, isMissing, hasResolved } = useSelect(
    (select) => {
      const queryArgs = ["postType", "pp_video_block", id];
      const hasResolved = select(coreStore).hasFinishedResolution(
        "getEntityRecord",
        queryArgs
      );
      const media = select(coreStore).getEntityRecord(...queryArgs);
      const canEdit = select(coreStore).canUserEditEntityRecord(...queryArgs);
      return {
        media,
        canEdit,
        isMissing: hasResolved && !media && id,
        hasResolved,
        isResolving: select(coreStore).isResolving(
          "getEntityRecord",
          queryArgs
        ),
      };
    },
    [id, clientId]
  );

  // we can edit the original if there is a block src,
  // the user can edit, and there is a src or provider_video_id.
  const canEditOriginal =
    !!hasSrc &&
    !!canEdit &&
    !!(media?.details?.src || media?.details?.provider_video_id);

  // set the selection based on if editing or not.
  useEffect(() => {
    // we need setimeout to ensure the block is selected after it is rendered.
    // this is because we swap between preview and regular inner blocks.
    setTimeout(() => {
      const blocks = select(blockEditorStore).getBlocks(clientId);
      const innerBlockClientId = blocks?.[0]?.innerBlocks?.[0]?.clientId;
      if (innerBlockClientId && isEditing && canEditOriginal) {
        selectBlock(innerBlockClientId);
      } else {
        selectBlock(clientId);
      }
    });
  }, [isEditing]);

  // make sure innermost block is always selected when this block is selected.
  // if we are not in edit mode, it won't have any inner blocks anyway.
  useEffect(() => {
    const blocks = select(blockEditorStore).getBlocks(clientId);
    const innerBlockClientId = blocks?.[0]?.innerBlocks?.[0]?.clientId;
    if (innerBlockClientId) {
      selectBlock(innerBlockClientId);
    }
  }, [isSelected]);

  if (!hasResolved) {
    return (
      <div {...blockProps}>
        <Placeholder>
          <Spinner />
        </Placeholder>
      </div>
    );
  }

  if (!id && context["presto-player/playlist-media-id"] !== undefined) {
    return (
      <Placeholder
        css={css`
          &.components-placeholder {
            min-height: 350px;
          }
        `}
        withIllustration
      />
    );
  }

  if (isMissing) {
    return (
      <div {...blockProps}>
        {__(
          "The selected media item has been deleted or is unavailable.",
          "presto-player"
        )}
      </div>
    );
  }

  if (!blocks.length) {
    return <ProvidersPlaceholder clientId={clientId} />;
  }

  if (!canEditOriginal || isEditing) {
    return (
      <EditContext.Provider value={{ isEditing, setIsEditing }}>
        <div {...innerBlocksProps} />
      </EditContext.Provider>
    );
  }

  return (
    <>
      {canEditOriginal && (
        <>
          <BlockControls>
            <Toolbar>
              <Button icon={edit} onClick={() => setIsEditing(true)}>
                {__("Edit Original", "presto-player")}
              </Button>
            </Toolbar>
          </BlockControls>
          <InspectorControls>
            <PanelBody>
              <Flex align="center" justify="flex-start">
                <Icon icon={symbolFilled} />
                <h2 class="block-editor-block-card__title">
                  {__("Synced", "presto-player")}
                </h2>
              </Flex>

              <BaseControl
                help={__(
                  "This item is synced with the media hub and can be reused across your site.",
                  "presto-player"
                )}
                css={css`
                  margin-bottom: 10px !important;
                `}
              ></BaseControl>

              <Button
                icon={edit}
                onClick={() => setIsEditing(true)}
                variant="secondary"
              >
                {__("Edit Original", "presto-player")}
              </Button>
            </PanelBody>
          </InspectorControls>

          <div {...blockProps}>
            <div {...blockPreviewProps} />
          </div>
        </>
      )}
    </>
  );
};
