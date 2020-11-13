local json = require("json")

local BATCH_SIZE = 256

local dump = {}
local records = {}
local counts = {}

--- Write the data to the output. This will clear the data and the counter of it.
-- @param name string: The name of the data to write.
local function write(name)
    if records[name] then
        print(string.format(">>>DUMP|%s>>>%s<<<DUMP<<<", name, json.encode(records[name]), name))
    end

    records[name] = nil
    counts[name] = nil
end

--- Adds data to the dump.
-- @param name string: The name of the data.
-- @param data table: The data to add.
function dump.add(name, data)
    if not data then
        return
    end

    if not records[name] then
        records[name] = {}
    end
    table.insert(records[name], data)

    counts[name] = (counts[name] or 0) + 1
    if counts[name] >= BATCH_SIZE then
        write(name)
    end
end

--- Flushes the dump. This will write any data not yet written to the output.
function dump.flush()
    for name in pairs(records) do
        write(name)
    end
end

return dump
