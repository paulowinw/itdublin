import {
  InnerBlocks,
  useBlockProps,
  useInnerBlocksProps,
} from "@wordpress/block-editor";
export default () => {
  const blockProps = useBlockProps({ slot: "preview" });
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    renderAppender: InnerBlocks.ButtonBlockAppender,
    templateLock: "all",
    template: [["presto-player/reusable-display"]],
    allowedBlocks: ["presto-player/reusable-display"],
  });

  return <div {...innerBlocksProps}></div>;
};
