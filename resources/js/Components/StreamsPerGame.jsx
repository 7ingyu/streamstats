import React, {useState} from 'react';

export default function StreamsPerGame ({ streamsPerGame }) {
  const highestStreamPerGame = Object.values(streamsPerGame).sort((a, b) => b - a)[0];
  const [showStreamsPerGame, setShowStreamsPerGame] = useState(false);

  return (
    <>
      <h2
          onClick={() => setShowStreamsPerGame(!showStreamsPerGame)}
          aria-label="Show Streams Per Game"
      >
          Streams Per Game{` `}
          {showStreamsPerGame ? <i class="bi bi-caret-up"></i> : <i className="bi bi-caret-down"></i>}
      </h2>
      {showStreamsPerGame && Object.entries(streamsPerGame).map(([name, count], i) => (
          <div className="row" key={i}>
              <div className="col-4">{name}</div>
              <div className="col-7">
                  <div
                      className="bg-primary h-100"
                      style={{ width: `${count / highestStreamPerGame * 100}%`}}
                  />
              </div>
              <div className="col-1">{count}</div>
          </div>
      ))}
    </>
  )
}