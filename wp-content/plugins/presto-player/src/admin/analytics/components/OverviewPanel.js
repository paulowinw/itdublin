const { __ } = wp.i18n;
const { Panel, TabPanel } = wp.components;

import TotalViewsGraph from "./TotalViewsGraph";
import TotalWatchGraph from "./TotalWatchGraph";

export default ({ startDate, endDate }) => {
  return (
    <Panel>
      <TabPanel
        className="presto-module-tabs"
        onSelect={() => {}}
        tabs={[
          {
            name: "views",
            title: __("Views", "presto-player"),
          },
          {
            name: "watch",
            title: __("Watch Time", "presto-player"),
          },
        ]}
      >
        {(tab) => {
          switch (tab.name) {
            case "views":
              return (
                <TotalViewsGraph startDate={startDate} endDate={endDate} />
              );
            case "watch":
              return (
                <TotalWatchGraph startDate={startDate} endDate={endDate} />
              );
            default:
              return <>Not Found</>;
          }
        }}
      </TabPanel>
    </Panel>
  );
};
