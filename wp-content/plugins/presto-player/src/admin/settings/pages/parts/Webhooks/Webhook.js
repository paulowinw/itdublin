import {
  Card,
  CardBody,
  Flex,
  FlexBlock,
  FlexItem,
  Spinner,
  ToolbarButton,
} from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { select, useDispatch, useSelect } from "@wordpress/data";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { store as noticesStore } from "@wordpress/notices";

import NewWebhook from "./NewWebhook";

export default ({ webhook: incomingWebhook }) => {
  const [busy, setBusy] = useState(false);
  const [open, setOpen] = useState(false);
  const { deleteEntityRecord, saveEntityRecord } = useDispatch(coreStore);
  const { createSuccessNotice, createErrorNotice } = useDispatch(noticesStore);

  const { webhook, loading, isSaving, isDeleting } = useSelect((select) => {
    const queryArgs = ["presto-player", "webhook", incomingWebhook?.id];
    const {
      getEditedEntityRecord,
      isSavingEntityRecord,
      isDeletingEntityRecord,
    } = select(coreStore);
    return {
      webhook: getEditedEntityRecord(...queryArgs),
      loading: select(coreStore).isResolving(
        "getEditedEntityRecord",
        queryArgs
      ),
      isSaving: isSavingEntityRecord(...queryArgs),
      isDeleting: isDeletingEntityRecord(...queryArgs),
    };
  }, []);

  const { name, url } = webhook || {};

  const deleteWebhook = async () => {
    try {
      const r = confirm(
        __("Are you sure you want to delete this webhook?", "presto-player")
      );
      if (!r) return;
      await deleteEntityRecord(
        "presto-player",
        "webhook",
        webhook?.id,
        undefined,
        { throwOnError: true }
      );
      createSuccessNotice(__("Webhook deleted.", "presto-player"), {
        type: "snackbar",
      });
    } catch (e) {
      console.error(e);
      createErrorNotice(
        e?.message || __("Something went wrong", "presto-player"),
        { type: "snackbar" }
      );
    }
  };

  if (loading) {
    return <Spinner />;
  }

  return (
    <Card>
      <CardBody>
        <Flex align="center">
          <FlexBlock>
            <strong>{name || __("Untitled webhook", "presto-player")}</strong>
            <br />
            {url}
          </FlexBlock>
          <FlexItem>
            <Flex align="center">
              {isSaving || isDeleting ? (
                <Spinner />
              ) : (
                <>
                  <ToolbarButton
                    icon="edit"
                    label="Edit"
                    onClick={() => setOpen(true)}
                  />
                  <ToolbarButton
                    icon="trash"
                    label={__("Delete", "presto-player")}
                    onClick={deleteWebhook}
                  />
                </>
              )}
            </Flex>
          </FlexItem>
        </Flex>
      </CardBody>
      {open && (
        <NewWebhook onClose={() => setOpen(false)} webhook={incomingWebhook} />
      )}
    </Card>
  );
};
