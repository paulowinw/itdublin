/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { SelectControl, Button, Flex, Icon } = wp.components;
const { useEffect, useState } = wp.element;
import LoadSelect from "../../components/LoadSelect";

import MailchimpConfig from "./MailchimpConfig";
import MailerLiteConfig from "./MailerLiteConfig";
import ActiveCampaignConfig from "./ActiveCampaignConfig";
import FluentCRMConfig from "./FluentCRMConfig";
import WebhooksConfig from "./WebhooksConfig";

export default ({ options, updateEmailState }) => {
  const [fetching, setFetching] = useState(false);
  const [settings, setSettings] = useState([
    { value: "none", label: __("None", "presto-player") },
  ]);
  const [error, setError] = useState("");

  const optionsMap = {
    presto_player_activecampaign: {
      label: "ActiveCampaign",
      value: "activecampaign",
    },
    presto_player_mailchimp: {
      label: "MailChimp",
      value: "mailchimp",
    },
    presto_player_mailerlite: {
      label: "MailerLite",
      value: "mailerlite",
    },
    presto_player_fluentcrm: {
      label: "FluentCRM",
      value: "fluentcrm",
    },
  };

  const fetchSettings = async () => {
    setFetching(true);
    setError("");
    try {
      const fetched = await wp.apiFetch({
        path: "wp/v2/settings",
      });
      let settingsToSet = settings;
      Object.keys(fetched).forEach((key) => {
        if (optionsMap?.[key] && fetched[key]?.connected) {
          settingsToSet = [...settingsToSet, ...[optionsMap[key]]];
        }
      });
      setSettings([
        ...settingsToSet,
        ...[{ label: __("Webhooks", "presto-player"), value: "webhooks" }],
      ]);
    } catch (e) {
      if (e?.message) {
        setError(e.message);
      }
    } finally {
      setFetching(false);
    }
  };

  const addProvider = () => {
    return (
      <Flex>
        <Button
          target="_blank"
          href="/wp-admin/edit.php?post_type=pp_video_block&page=presto-player-settings#/integrations"
          isSecondary
          isSmall
        >
          {__("Connect a provider", "presto-player")}
        </Button>
        <Button
          isSmall
          onClick={(e) => {
            e.preventDefault();
            fetchSettings();
          }}
        >
          <Icon icon="update" />
        </Button>
      </Flex>
    );
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const emailProviderOptions = () => {
    const found = settings.find(
      (setting) => setting.value === options?.provider
    );
    if (!Object.keys(found || {}).length) {
      return;
    }

    switch (options?.provider) {
      case "webhooks":
        return (
          <WebhooksConfig
            options={options}
            updateEmailState={updateEmailState}
          />
        );
      case "mailchimp":
        return (
          <MailchimpConfig
            options={options}
            updateEmailState={updateEmailState}
          />
        );
      case "mailerlite":
        return (
          <MailerLiteConfig
            options={options}
            updateEmailState={updateEmailState}
          />
        );
      case "activecampaign":
        return (
          <ActiveCampaignConfig
            options={options}
            updateEmailState={updateEmailState}
          />
        );
      case "fluentcrm":
        return (
          <FluentCRMConfig
            options={options}
            updateEmailState={updateEmailState}
          />
        );
    }
  };

  if (fetching) {
    return <LoadSelect />;
  }

  return (
    <div>
      {error}
      {settings.length > 1 ? (
        <div>
          <SelectControl
            label={__("Choose an email provider", "presto-player")}
            value={options?.provider}
            options={settings}
            onChange={(provider) => updateEmailState({ provider })}
          />
          {emailProviderOptions()}
        </div>
      ) : (
        addProvider()
      )}
    </div>
  );
};
