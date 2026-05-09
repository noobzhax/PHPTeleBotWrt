#!/bin/sh

URL="$1"
if [ -z "$URL" ]; then
    echo "Please retry with valid url | Example : /aria2add http://file.server.com/file.mp4"
else
    RPC_URL=$(uci get aria2.main._rpc_url)
    SAFE_URL=$(echo "$URL" | sed 's/"/\\"/g')
    curl "$RPC_URL" -X POST --data '{"jsonrpc": "2.0","id":"foo", "method": "aria2.addUri", "params":[["'"$SAFE_URL"'"]]}'
    echo ""
    echo "Task added to Aria2 - Check download status /aria2stats"
fi
