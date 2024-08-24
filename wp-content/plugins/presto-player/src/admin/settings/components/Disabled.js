const { __ } = wp.i18n;
const { useState } = wp.element;
const { Modal, Button } = wp.components;

export default ({ children, disabled }) => {
  const [dialog, setDialog] = useState(false);

  if (!disabled) {
    return <div>{children}</div>;
  }

  return (
    <div>
      <div
        className="presto-options__disabled-overlay"
        onClick={(e) => {
          setDialog(true);
          e.preventDefault();
          return false;
        }}
      >
        <div>{children}</div>
      </div>
      {!!dialog && (
        <Modal title={disabled?.title} onRequestClose={() => setDialog(false)}>
          <h2>{disabled?.heading}</h2>
          <p>{disabled?.message}</p>
          <Button href={disabled?.link} target="_blank" isPrimary>
            {__("Learn More", "presto-player")}
          </Button>
        </Modal>
      )}
    </div>
  );
};
