export default ({ icon, title, id, selectBlock, i }) => {
  return (
    <button
      type="button"
      id={`video-${id}`}
      tabIndex={i}
      onClick={selectBlock}
      role="option"
      className="components-button block-editor-block-types-list__item editor-block-list-item-kadence-spacer"
    >
      <span className="block-editor-block-types-list__item-icon">
        <span className="block-editor-block-icon has-colors">{icon}</span>
      </span>
      <span className="block-editor-block-types-list__item-title">{title}</span>
    </button>
  );
};
