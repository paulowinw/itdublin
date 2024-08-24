export default ({ children, title, description }) => {
  return (
    <div className="presto-flow presto-settings__page">
      <div className="presto-flow" style={{ "--presto-flow-space": "1em" }}>
        {title && <h1>{title}</h1>}
        {description && <p>{description}</p>}
      </div>

      <div className="presto-settings__body ">
        <div className="presto-flow">{children}</div>
      </div>
    </div>
  );
};
