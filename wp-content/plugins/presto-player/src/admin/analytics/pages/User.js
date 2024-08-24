/** @jsx jsx */

const { __ } = wp.i18n;

const { Flex, FlexBlock, FlexItem, Spinner, Button } = wp.components;

import { history } from "@/router/context";
import DatePicker from "../components/DatePicker";
import TopVideos from "../components/TopVideos";
import TotalVideoViewsByUser from "../components/TotalVideoViewsByUser";
import VideoAverageWatchTimeByUser from "../components/VideoAverageWatchTimeByUser";
import VideoTotalWatchTimeByUser from "../components/VideoTotalWatchTimeByUser";

const { useEffect, useState } = wp.element;
const { apiFetch } = wp;

import { css, jsx } from "@emotion/core";

const User = ({ route, startDate, endDate, setStartDate, setEndDate }) => {
  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState({});
  const [error, setError] = useState("");

  const back = () => {
    const { search } = history.location;
    history.push(`${search}#/`);
  };

  const getUser = async () => {
    setLoading(true);
    try {
      let user = await apiFetch({
        url: `/wp-json/wp/v2/users/${route?.params?.id}?context=edit`,
      });
      setUser(user);
    } catch (e) {
      if (e.code === "rest_no_route") {
        setError("User Not Found");
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    getUser();
  }, []);

  if (error) {
    return (
      <div className="presto-flow">
        <Flex>
          <FlexBlock>
            <h2>{error}</h2>
          </FlexBlock>
        </Flex>
      </div>
    );
  }

  return (
    <div className="presto-flow">
      <Flex>
        <FlexBlock>
          <Button isSecondary onClick={back}>
            &larr; {__("Back to Dashboard", "presto-player")}
          </Button>
        </FlexBlock>
      </Flex>
      <Flex wrap>
        <FlexBlock>
          {loading ? (
            <Spinner />
          ) : (
            <div
              css={css`
                display: flex;
                align-items: center;

                .presto__edit-user-button {
                  opacity: 0;
                  visibility: hidden;
                  transition: opacity 0.3s ease;
                }

                &:hover {
                  .presto__edit-user-button {
                    opacity: 1;
                    visibility: visible;
                  }
                }
              `}
            >
              <div>
                <h1 className="presto-dashboard__title">{user?.name}</h1>
                <p
                  css={css`
                    margin-top: -10px;
                    opacity: 0.65;
                  `}
                >
                  {user?.email}
                </p>
              </div>
              {!!user.id && (
                <div
                  className="presto__edit-user-button"
                  css={css`
                    margin: 0 20px;
                  `}
                >
                  <Button
                    href={`/wp-admin/user-edit.php?user_id=${user?.id}`}
                    isSecondary
                    isSmall
                  >
                    {__("View Profile", "presto-player")} &rarr;
                  </Button>
                </div>
              )}
            </div>
          )}
        </FlexBlock>
        <FlexItem>
          <DatePicker
            startDate={startDate}
            setStartDate={setStartDate}
            endDate={endDate}
            setEndDate={setEndDate}
          />
        </FlexItem>
      </Flex>

      <div className="presto-dashboard presto-flow">
        <div className="presto-dashboard__row">
          <div className="presto-dashboard__item">
            <TotalVideoViewsByUser
              userId={route?.params?.id}
              startDate={startDate}
              endDate={endDate}
            />
          </div>
          <div className="presto-dashboard__item">
            <VideoAverageWatchTimeByUser
              userId={route?.params?.id}
              startDate={startDate}
              endDate={endDate}
            />
          </div>
          <div className="presto-dashboard__item">
            <VideoTotalWatchTimeByUser
              userId={route?.params?.id}
              startDate={startDate}
              endDate={endDate}
            />
          </div>
        </div>
        <div className="presto-dashboard__row">
          <div className="presto-dashboard__item is-large">
            <TopVideos
              startDate={startDate}
              endDate={endDate}
              userId={route?.params?.id}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default User;
