const { Modal, Button } = wp.components;
const { dispatch, useSelect } = wp.data;
import ProBadge from "@/admin/blocks/shared/components/ProBadge";

export default function () {
  const closeModal = () => {
    dispatch("presto-player/player").setProModal(false);
  };

  const open = useSelect((select) => {
    return select("presto-player/player").proModal();
  });

  return open ? (
    <Modal title={"Pro Feature"} onRequestClose={closeModal}>
      <h2>
        Unlock Presto Player <ProBadge />
      </h2>
      <p>Get this feature and more with the Pro version of Presto Player!</p>
      <Button href="https://prestoplayer.com" target="_blank" isPrimary>
        Learn More
      </Button>
    </Modal>
  ) : (
    ""
  );
}
