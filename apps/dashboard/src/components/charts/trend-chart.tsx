"use client"

import { Area, Bar, CartesianGrid, ComposedChart, LabelList, Line, XAxis, YAxis } from "recharts"
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from "@/components/ui/chart"
import { formatNumber } from "@/lib/format"

export type TrendPoint = {
  label: string
  value: number
  /** Reference range [low, high] drawn as a shaded band. */
  band?: [number, number]
  /** Secondary bar series on the right axis. */
  bar?: number
}

type TrendChartProps = {
  data: TrendPoint[]
  config: ChartConfig
  /** Accessible summary of what the chart shows. */
  label: string
  yDomain: [number, number]
  /** Text shown in the forest callout above the highlighted point. */
  callout?: string
  /** Point emphasised with a filled dot and the callout. Defaults to the last one. */
  highlightIndex?: number
  showBand?: boolean
  showLine?: boolean
  showBars?: boolean
  barMax?: number
}

/** Line over an optional reference band and bar series: growth, GMQ, mortality. */
export function TrendChart({
  data,
  config,
  label,
  yDomain,
  callout,
  highlightIndex = data.length - 1,
  showBand = true,
  showLine = true,
  showBars = false,
  barMax = 6,
}: TrendChartProps) {
  return (
    <ChartContainer config={config} role="img" aria-label={label} className="aspect-auto h-60 w-full">
      <ComposedChart data={data} margin={{ top: 32, right: 8, left: 0, bottom: 0 }}>
        <CartesianGrid vertical={false} strokeDasharray="2 4" />
        <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={8} />
        <YAxis domain={yDomain} tickLine={false} axisLine={false} width={40} tickFormatter={(v: number) => formatNumber(v)} />
        {showBars && <YAxis yAxisId="bars" orientation="right" domain={[0, barMax]} tickLine={false} axisLine={false} width={28} />}
        <ChartTooltip content={<ChartTooltipContent />} />

        {showBand && (
          <Area dataKey="band" type="linear" stroke="none" fill="var(--color-band)" fillOpacity={0.18} isAnimationActive={false} />
        )}
        {showBars && <Bar dataKey="bar" yAxisId="bars" fill="var(--color-bar)" radius={3} barSize={14} />}
        {showLine && (
          <Line
            dataKey="value"
            type="linear"
            stroke="var(--color-value)"
            strokeWidth={2.5}
            isAnimationActive={false}
            dot={({ cx, cy, index }) => (
              <circle
                key={index}
                cx={cx}
                cy={cy}
                r={index === highlightIndex ? 6 : 4}
                fill={index === highlightIndex ? "var(--color-value)" : "var(--card)"}
                stroke="var(--color-value)"
                strokeWidth={2}
              />
            )}
          >
            {callout && (
              <LabelList
                dataKey="value"
                content={(props) => (props.index === highlightIndex ? <Callout x={props.x} y={props.y} text={callout} /> : null)}
              />
            )}
          </Line>
        )}
      </ComposedChart>
    </ChartContainer>
  )
}

function Callout({ x, y, text }: { x?: number | string; y?: number | string; text: string }) {
  const width = text.length * 7 + 18
  const cx = Number(x)
  const cy = Number(y)

  return (
    <g>
      <rect x={cx - width / 2} y={cy - 34} width={width} height={24} rx={6} fill="var(--forest)" />
      <text x={cx} y={cy - 18} textAnchor="middle" fontSize={12} fontWeight={600} fill="#fff">
        {text}
      </text>
    </g>
  )
}
