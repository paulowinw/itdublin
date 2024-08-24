/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { useBlockProps, useInnerBlocksProps } from "@wordpress/block-editor";
import { createBlock } from "@wordpress/blocks";
import { store as blockEditorStore } from "@wordpress/block-editor";
import { store as coreStore } from "@wordpress/core-data";
import { useDispatch, useSelect } from "@wordpress/data";
import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import SelectMediaDropdown from "../../shared/components/SelectMediaDropdown";
import { Icon, plus } from "@wordpress/icons";
import { Button, VisuallyHidden } from "@wordpress/components";
import Create from "./create";

export default ({ clientId, isSelected }) => {
  const { selectBlock, insertBlock } = useDispatch(blockEditorStore);
  const { saveEntityRecord } = useDispatch(coreStore);
  const blockProps = useBlockProps({ slot: "list" });
  const [modal, setModal] = useState(false);

  const label = __("Add Media", "presto-player");

  // get currently selected item ids.
  const selectedItemIds = useSelect(
    (select) =>
      (select("core/block-editor").getBlocks(clientId) || []).map((i) =>
        parseInt(i.attributes.id)
      ),
    [clientId]
  );

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    renderAppender: () => (
      <SelectMediaDropdown
        onCreate={() => setModal(true)}
        value={selectedItemIds}
        popoverProps={{ placement: "bottom-center" }}
        onSelect={(video) =>
          insertBlock(
            createBlock("presto-player/playlist-list-item", {
              id: video?.id,
              title: video?.title,
            }),
            999999,
            clientId
          )
        }
        css={css`
          width: 100%;
          padding: 5px;
          box-sizing: border-box !important;
        `}
        renderToggle={({ isOpen, onToggle }) => (
          <Button
            className={"block-editor-button-block-appender"}
            onClick={onToggle}
            aria-haspopup={true}
            aria-expanded={isOpen}
            label={label}
            css={css`
              box-sizing: border-box !important;
            `}
          >
            <VisuallyHidden as="span">{label}</VisuallyHidden>
            <Icon icon={plus} />
          </Button>
        )}
      />
    ),
    allowedBlocks: ["presto-player/reusable-display"],
    templateLock: false,
  });

  const onCreate = async (title) => {
    const {
      id,
      title: { raw },
    } = await saveEntityRecord(
      "postType",
      "pp_video_block",
      {
        title,
        status: "publish",
        content: `<!-- wp:presto-player/reusable-edit -->
        <div class="wp-block-presto-player-reusable-edit"></div>
        <!-- /wp:presto-player/reusable-edit -->`,
      },
      { throwOnError: true }
    );
    insertBlock(
      createBlock("presto-player/playlist-list-item", {
        id,
        title: raw,
      }),
      999999,
      clientId
    );
  };

  const {
    listCount,
    heading,
    listTextSingular,
    listTextPlural,
    parentClientId,
  } = useSelect(
    (select) => {
      const parentClientId =
        select("core/block-editor").getBlockHierarchyRootClientId(clientId);
      const parentAttributes =
        select("core/block-editor").getBlockAttributes(parentClientId);
      return {
        listCount: select("core/block-editor").getBlocks(clientId).length,
        heading: parentAttributes?.heading,
        listTextPlural: parentAttributes?.listTextPlural,
        listTextSingular: parentAttributes?.listTextSingular,
        parentClientId: parentClientId,
      };
    },
    [clientId]
  );

  // select parent block if this block is selected.
  useEffect(() => {
    if (isSelected) {
      selectBlock(parentClientId);
    }
  }, [isSelected]);

  return (
    <>
      <div slot="title">{heading}</div>

      <div slot="count">
        {listCount} {listCount > 1 ? listTextPlural : listTextSingular}
      </div>

      <div {...innerBlocksProps}></div>

      {modal && (
        <Create onRequestClose={() => setModal(false)} onCreate={onCreate} />
      )}
    </>
  );
};
