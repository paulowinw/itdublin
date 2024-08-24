import { __ } from "@wordpress/i18n";
import {
  Card,
  Flex,
  FlexBlock,
  FlexItem,
  Spinner,
} from "@wordpress/components";
import { store as noticesStore } from "@wordpress/notices";
import { useDispatch, useSelect } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";
import { useState, useEffect } from "@wordpress/element";

import { Router, Link, Route } from "@/router";
import { routes } from "./routes";

import SaveButton from "./components/SaveButton";
import Notices from "./components/Notices";
import useSave from "../../hooks/useSave";
import General from "./pages/General";
import Performance from "./pages/Performance";
import Integrations from "./pages/Integrations";

function App() {
  const { createSuccessNotice, createErrorNotice } = useDispatch(noticesStore);
  const [loaded, setLoaded] = useState(false);

  // scroll top on history change
  window.onhashchange = () => {
    window.scrollTo(0, 0);
  };

  const { save } = useSave();

  /**
   * Form is submitted.
   */
  const onSubmit = async () => {
    try {
      await save();
      createSuccessNotice(__("Settings Updated", "presto-player"), {
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

  const loading = useSelect((select) => {
    const queryArgs = ["root", "site"];
    select(coreStore).getEntityRecords(...queryArgs);
    return !select(coreStore)?.hasFinishedResolution?.(
      "getEntityRecords",
      queryArgs
    );
  });

  useEffect(() => {
    if (!loading) {
      setLoaded(true);
    }
  }, [loading]);

  if (!loaded) {
    return (
      <div className="presto-settings__loading">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="presto-settings">
      <Router routes={routes} defaultRoute={routes?.general?.path}>
        <Card className="presto-settings__navigation">
          <Flex>
            <FlexBlock>
              <div
                role="tablist"
                aria-orientation="horizontal"
                className="components-tab-panel__tabs"
              >
                <Link
                  to={routes?.general?.path}
                  type="button"
                  role="tab"
                  activeClassName="is-active"
                  className="components-button components-tab-panel__tabs-item presto-player__nav-general"
                >
                  {__("General", "presto-player")}
                </Link>
                <Link
                  to={routes?.integrations?.path}
                  type="button"
                  role="tab"
                  activeClassName="is-active"
                  className="components-button components-tab-panel__tabs-item presto-player__nav-integrations"
                >
                  {__("Integrations", "presto-player")}
                </Link>
                <Link
                  to={routes?.performance?.path}
                  type="button"
                  role="tab"
                  activeClassName="is-active"
                  className="components-button components-tab-panel__tabs-item presto-player__nav-performance"
                >
                  {__("Performance", "presto-player")}
                </Link>
              </div>
            </FlexBlock>
            <FlexItem>
              <SaveButton onSave={onSubmit} style={{ marginRight: "8px" }}>
                {__("Update Settings", "presto-player")}
              </SaveButton>
            </FlexItem>
          </Flex>
        </Card>

        <Route path={routes?.general?.path}>
          <General />
        </Route>
        <Route path={routes?.integrations?.path}>
          <Integrations />
        </Route>
        <Route path={routes?.performance?.path}>
          <Performance />
        </Route>
      </Router>

      <Notices className="presto-settings-page-notices" />
    </div>
  );
}

export default App;
