const { __ } = wp.i18n;

const {
  Button,
  Panel,
  PanelBody,
  Flex,
  Modal,
  FlexBlock,
  FlexItem,
  PanelRow,
} = wp.components;

const { useState } = wp.element;

export default ({
  connected,
  title,
  children,
  onConnect,
  isBusy,
  connectButtonText,
  disconnectButtonText,
  onDisconnect,
}) => {
  const [confirm, setConfirm] = useState(false);

  return (
    <Panel>
      <PanelBody
        title={
          <Flex>
            <FlexBlock>{title}</FlexBlock>
            <FlexItem>
              {connected ? (
                <Button isSmall isPrimary style={{ marginRight: "30px" }}>
                  {__("Connected", "presto-player")}
                </Button>
              ) : (
                <Button isSmall isSecondary style={{ marginRight: "30px" }}>
                  {__("Not Connected", "presto-player")}
                </Button>
              )}
            </FlexItem>
          </Flex>
        }
        initialOpen={false}
      >
        <form
          onSubmit={(e) => {
            e.preventDefault();
            onConnect();
          }}
          disabled={isBusy}
        >
          {children}
          <PanelRow>
            <div>
              {!connected ? (
                <Button
                  isPrimary
                  isBusy={isBusy}
                  disabled={isBusy}
                  type="submit"
                >
                  {connectButtonText
                    ? connectButtonText
                    : __("Connect", "presto-player")}
                </Button>
              ) : (
                <div>
                  {" "}
                  <Button
                    isSecondary
                    isBusy={isBusy}
                    disabled={isBusy}
                    onClick={(e) => {
                      e.preventDefault();
                      setConfirm(true);
                    }}
                  >
                    {disconnectButtonText
                      ? disconnectButtonText
                      : __("Disconnect", "presto-player")}
                  </Button>
                </div>
              )}
            </div>
          </PanelRow>
        </form>
      </PanelBody>

      {confirm && (
        <Modal
          className="presto-player__modal-confirm"
          title={__("Are you sure?", "presto-player")}
          style={{ "max-width": "350px" }}
          onRequestClose={() => setConfirm(false)}
        >
          <p>
            {__(
              "Are you sure you want to disconnect this integration?",
              "presto-player"
            )}
          </p>
          <Button
            className="presto-player__modal-confirm-button"
            isDestructive
            onClick={() => {
              onDisconnect && onDisconnect();
              setConfirm(false);
            }}
          >
            {__("Disconnect", "presto-player")}
          </Button>
          <Button onClick={() => setConfirm(false)}>
            {__("Cancel", "presto-player")}
          </Button>
        </Modal>
      )}
    </Panel>
  );
};
