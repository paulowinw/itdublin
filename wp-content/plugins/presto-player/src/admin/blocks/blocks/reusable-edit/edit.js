/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import {
  store as blockEditorStore,
  useBlockProps,
  useInnerBlocksProps,
} from "@wordpress/block-editor";
import { select, useSelect, useDispatch } from "@wordpress/data";
import { useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import ProvidersPlaceholder from "../../shared/ProvidersPlaceholder/ProvidersPlaceholder";
import { useContext } from "@wordpress/element";
import EditContext from "../reusable-display/context";

export default ({ clientId, isSelected, context }) => {
  const { selectBlock } = useDispatch(blockEditorStore);
  const { setTemplateValidity } = useDispatch(blockEditorStore);
  const { isEditing } = useContext(EditContext);
  const innerBlocks = useSelect(
    (select) => select(blockEditorStore).getBlock(clientId).innerBlocks
  );

  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    templateLock: isEditing ? "all" : false, // lock the template if we are in the editing context.
    renderAppender: false,
  });

  setTemplateValidity(true);

  useEffect(() => {
    // if this is selected, and we are in the playlist context, select the inner block.
    if (isSelected && context["presto-player/playlist-media-id"]) {
      const blockOrder = select(blockEditorStore).getBlockOrder(clientId);
      const firstInnerBlockClientId = blockOrder[0];
      if (firstInnerBlockClientId) {
        selectBlock(firstInnerBlockClientId);
      }
    }
  }, [isSelected]);

  if (!innerBlocks?.length) {
    return (
      <div {...blockProps}>
        <ProvidersPlaceholder clientId={clientId} />
        <div {...innerBlocksProps} />
      </div>
    );
  }

  return <div {...innerBlocksProps} />;
};
