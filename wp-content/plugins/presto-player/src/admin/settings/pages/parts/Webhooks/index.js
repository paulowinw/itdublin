import {
  Button,
  Flex,
  FlexBlock,
  Panel,
  PanelBody,
} from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { useSelect } from "@wordpress/data";
import { useState } from "@wordpress/element";
import { __, _n, sprintf } from "@wordpress/i18n";

import NewWebhook from "./NewWebhook";
import Webhook from "./Webhook";

export default () => {
  const [open, setOpen] = useState(false);
  const { webhooks, loading } = useSelect((select) => {
    const queryArgs = ["presto-player", "webhook"];
    return {
      webhooks: select(coreStore).getEntityRecords(...queryArgs),
      loading: select(coreStore).isResolving("getEntityRecords", queryArgs),
    };
  }, []);

  return (
    <Panel>
      <PanelBody
        title={
          <Flex>
            <FlexBlock>{__("Webhooks", "presto-player")}</FlexBlock>
            {!!webhooks?.length && (
              <Button isSmall isPrimary style={{ marginRight: "30px" }}>
                {sprintf(__("%d connected"), webhooks?.length)}
              </Button>
            )}
          </Flex>
        }
        initialOpen={false}
      >
        {(webhooks || []).map((webhook) => {
          return (
            <Webhook webhook={webhook} key={webhook?.id} loading={loading} />
          );
        })}

        <br />

        <Button
          isSecondary
          onClick={() => {
            setOpen(true);
          }}
        >
          {__("Create New WebHook", "presto-player")}
        </Button>

        {open && <NewWebhook onClose={() => setOpen(false)} />}
      </PanelBody>
    </Panel>
  );
};
