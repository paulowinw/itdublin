import { __ } from "@wordpress/i18n";
import { Button, Flex, Modal, TextControl } from "@wordpress/components";
import { useState } from "@wordpress/element";

export default ({ onCreate, onRequestClose }) => {
  const [title, setTitle] = useState("");

  return (
    <Modal
      title={__("Add New Playlist Item", "presto-player")}
      onRequestClose={onRequestClose}
    >
      <Flex direction="column" gap={4}>
        <TextControl
          value={title}
          onChange={(title) => setTitle(title)}
          placeholder={__("Title", "presto-player")}
          required
          label={__("Title", "presto-player")}
          autoFocus
        />
        <Flex justify="start" align="center">
          <Button
            style={{ margin: 0 }}
            variant="primary"
            onClick={() => onCreate(title)}
          >
            {__("Create", "presto-player")}
          </Button>
          <Button
            variant="tertiary"
            style={{ margin: 0 }}
            onClick={onRequestClose}
          >
            {__("Cancel", "presto-player")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};
