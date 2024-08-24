const { useState } = wp.element;

import { Route, Router } from "@/router";

import AnalyticsUpgrade from "./pages/AnalyticsUpgrade";
import Dashboard from "./pages/Dashboard";

import User from "./pages/User";
import Video from "./pages/Video";
import { routes } from "./routes";

export default () => {
  const scrollToTop = () => {
    window.scrollTo(0, 0);
  };

  const [startDate, setStartDate] = useState(
    new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)
  );
  const [endDate, setEndDate] = useState(new Date());

  if (!prestoPlayer?.isPremium) {
    return (
      <div className="presto-dashboard__content">
        <AnalyticsUpgrade />
      </div>
    );
  }

  return (
    <div className="presto-dashboard__content">
      <Router routes={routes}>
        <Route path={routes.dashboard.path} onRoute={scrollToTop}>
          <Dashboard
            startDate={startDate}
            endDate={endDate}
            setStartDate={setStartDate}
            setEndDate={setEndDate}
          />
        </Route>
        <Route path={routes.video.path} onRoute={scrollToTop}>
          <Video
            startDate={startDate}
            endDate={endDate}
            setStartDate={setStartDate}
            setEndDate={setEndDate}
          />
        </Route>
        <Route path={routes.user.path} onRoute={scrollToTop}>
          <User
            startDate={startDate}
            endDate={endDate}
            setStartDate={setStartDate}
            setEndDate={setEndDate}
          />
        </Route>
      </Router>
    </div>
  );
};
